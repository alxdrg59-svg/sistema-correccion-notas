<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// =====================================================
// CONTROLADOR DEL DOCENTE
// Maneja las acciones que el docente puede realizar:
//   - Ver su bandeja de solicitudes (dashboard)
//   - Ver el detalle de una solicitud especifica
//   - Aprobar o rechazar una solicitud con evidencia opcional
//
// El docente es el primer nivel de revision en el flujo:
// Estudiante → Docente → Coordinador → Admin
//
// NOTA: Las solicitudes de excepcion ahora las crea el
// estudiante (ya no el docente). El docente solo las
// aprueba o rechaza como cualquier otra solicitud.
// =====================================================
class DocenteController extends Controller
{
    // =====================================================
    // DASHBOARD: Lista todas las solicitudes asignadas al
    // docente logueado. Incluye tanto solicitudes normales
    // como excepciones (se distinguen con el campo es_excepcion).
    // Se ordenan por fecha, de la mas reciente a la mas antigua.
    // =====================================================
    public function index()
    {
        $solicitudes = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->where('solicitudes_correccion.docente_id', Auth::id())
            ->select(
                'solicitudes_correccion.id',
                'solicitudes_correccion.evaluacion',
                'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual',
                'solicitudes_correccion.estado',
                'solicitudes_correccion.fecha_solicitud',
                'solicitudes_correccion.es_excepcion',
                'materias.nombre as materia_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet'
            )
            ->orderBy('solicitudes_correccion.fecha_solicitud', 'desc')
            ->get();

        return view('docente.dashboard', compact('solicitudes'));
    }

    // =====================================================
    // VER DETALLE: Muestra la solicitud completa al docente
    //
    // Verifica que la solicitud pertenezca al docente logueado
    // para evitar que un docente vea solicitudes de otro.
    //
    // Carga informacion adicional:
    //   - Si el coordinador ya tomo una decision (para bloquear
    //     la edicion del docente si es asi)
    //   - La ultima decision del docente (si ya reviso antes)
    //   - Evidencia del docente (si subio alguna)
    //   - Evidencia del estudiante (si adjunto alguna)
    // =====================================================
    public function verDetalle($id)
    {
        // Traer la solicitud con JOIN a materias y estudiantes
        // Solo permite ver si el docente logueado es el asignado
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('solicitudes_correccion.docente_id', Auth::id())
            ->select(
                'solicitudes_correccion.id',
                'solicitudes_correccion.seccion',
                'solicitudes_correccion.evaluacion',
                'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual',
                'solicitudes_correccion.motivo',
                'solicitudes_correccion.estado',
                'solicitudes_correccion.fecha_solicitud',
                'solicitudes_correccion.es_excepcion',
                'materias.nombre as materia_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet'
            )
            ->first();

        if (!$solicitud) {
            return redirect('/docente/dashboard')
                ->with('error', 'Solicitud no encontrada o no tienes permiso para verla.');
        }

        // Verificar si el coordinador ya tomo una decision.
        // Si el coordinador ya actuo, el docente no puede editar su revision.
        $coordinadorYaActuo = DB::table('aprobaciones')
            ->where('solicitud_id', $id)
            ->where(function($q) {
                $q->where('accion', 'like', '%coordinador%');
            })
            ->exists();

        // Buscar la ultima decision del docente para esta solicitud.
        // Si existe, se muestra en la vista como "Tu revision anterior".
        $decisionDocente = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%docente%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select(
                'aprobaciones.id',
                'aprobaciones.accion',
                'aprobaciones.comentario',
                'aprobaciones.fecha',
                'usuarios.nombre as actor_nombre'
            )
            ->first();

        // Buscar todas las evidencias que haya subido el docente
        $evidencias = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha', 'desc')
            ->get();

        // Buscar todas las evidencias que haya subido el estudiante
        $evidenciasEstudiante = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('descripcion', 'like', '%estudiante%')
            ->orderBy('fecha', 'desc')
            ->get();

        return view('docente.detalle_solicitud', compact(
            'solicitud',
            'coordinadorYaActuo',
            'decisionDocente',
            'evidencias',
            'evidenciasEstudiante'
        ));
    }

    // =====================================================
    // PROCESAR DECISION: Aprueba o rechaza la solicitud
    //
    // Reglas de la decision:
    //   - Si APRUEBA:
    //     - El estado cambia a 'pendiente_coordinador'
    //     - Debe indicar la nota sugerida para el admin
    //     - Puede adjuntar evidencia (opcional)
    //     - El comentario es opcional
    //
    //   - Si RECHAZA:
    //     - El estado cambia a 'rechazado_docente'
    //     - El comentario es obligatorio (minimo 10 caracteres)
    //     - Puede adjuntar evidencia (opcional)
    //
    // Restricciones de seguridad:
    //   - Solo el docente asignado puede procesar la solicitud
    //   - Si el coordinador ya actuo, no se puede modificar
    //
    // Si el docente esta editando una decision anterior,
    // primero se borra la evidencia vieja (del storage y de la BD)
    // antes de guardar la nueva.
    // =====================================================
    public function procesarDecision(Request $request, $id)
    {
        // Verificar que la solicitud pertenece al docente logueado
        $solicitud = DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->where('docente_id', Auth::id())
            ->first();

        if (!$solicitud) {
            return redirect('/docente/dashboard')
                ->with('error', 'No tienes permiso para procesar esta solicitud.');
        }

        // Verificar que el coordinador no haya actuado aun
        // Si ya lo hizo, la decision del docente queda bloqueada
        $coordinadorYaActuo = DB::table('aprobaciones')
            ->where('solicitud_id', $id)
            ->where('accion', 'like', '%coordinador%')
            ->exists();

        if ($coordinadorYaActuo) {
            return redirect('/docente/solicitud/' . $id)
                ->with('error', 'El coordinador ya evaluó esta solicitud. No puedes modificar tu decisión.');
        }

        // Armar reglas de validacion segun la decision tomada
        $rules = [
            'decision'            => 'required|in:aprobado,rechazado',
            'comentario'          => 'nullable|string|max:500',
            // La nota sugerida solo es obligatoria al aprobar
            'nota_sugerida_admin' => $request->decision === 'aprobado'
                                    ? 'required|string|min:1|max:500'
                                    : 'nullable|string|max:500',
            'evidencias'          => 'nullable|array',
            'evidencias.*'        => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // Si rechaza, el comentario pasa a ser obligatorio
        if ($request->decision === 'rechazado') {
            $rules['comentario'] = 'required|string|min:10|max:500';
        }

        $request->validate($rules, [
            'comentario.required' => 'Debes escribir una justificación para rechazar la solicitud.',
            'comentario.min'      => 'La justificación debe tener al menos 10 caracteres.',
            'nota_sugerida_admin.required'   => 'Debes indicar la nota sugerida para el administrador.',
            'nota_sugerida_admin.min'      => 'La nota sugerida debe tener al menos 1 caracter.',
            'evidencias.*.mimes'  => 'Solo se permiten archivos JPG, PNG o PDF.',
            'evidencias.*.max'    => 'Cada archivo no puede superar los 5MB.',
        ]);

        // Si el docente esta editando su decision, borrar todas las evidencias
        // anteriores para evitar que se acumulen archivos viejos en el storage
        $evidenciasPrevias = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', Auth::id())
            ->get();

        foreach ($evidenciasPrevias as $evidenciaPrevia) {
            Storage::disk('gcs')->delete($evidenciaPrevia->archivo);
            DB::table('evidencias')->where('id', $evidenciaPrevia->id)->delete();
        }

        // Si el docente subio nuevos archivos de evidencia, guardar cada uno
        if ($request->hasFile('evidencias')) {
            foreach ($request->file('evidencias') as $index => $archivo) {
                if ($archivo->isValid()) {
                    $extension   = $archivo->getClientOriginalExtension();
                    $nombreArchivo = 'evidencia_docente_' . $id . '_' . time() . '_' . $index . '.' . $extension;
                    $rutaArchivo   = $archivo->storeAs('evidencias', $nombreArchivo, 'gcs');

                    DB::table('evidencias')->insert([
                        'solicitud_id' => $id,
                        'usuario_id'   => Auth::id(),
                        'archivo'      => $rutaArchivo,
                        'descripcion'  => 'Evidencia adjuntada por docente',
                        'fecha'        => now(),
                    ]);
                }
            }
        }

        // Definir el nuevo estado y el texto de la accion segun la decision
        if ($request->decision === 'aprobado') {
            $nuevoEstado = 'pendiente_coordinador';
            $accionTexto = 'Aprobado por docente';
        } else {
            $nuevoEstado = 'rechazado_docente';
            $accionTexto = 'Rechazado por docente';
        }

        // Guardar la decision en la tabla de aprobaciones.
        // La nota sugerida solo se guarda si la decision fue aprobar.
        DB::table('aprobaciones')->insert([
            'solicitud_id'        => $id,
            'usuario_id'          => Auth::id(),
            'accion'              => $accionTexto,
            'comentario'          => $request->comentario ?? null,
            'nota_sugerida_admin' => $request->decision === 'aprobado'
                                    ? ($request->nota_sugerida_admin ?? null)
                                    : null,
            'fecha'               => now(),
        ]);

        // Actualizar el estado de la solicitud en la tabla principal
        DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->update(['estado' => $nuevoEstado]);

        $mensaje = $request->decision === 'aprobado'
            ? 'Solicitud aprobada. Ha sido enviada al coordinador de facultad.'
            : 'Solicitud rechazada correctamente.';

        return redirect('/docente/dashboard')->with('success', $mensaje);
    }
}
