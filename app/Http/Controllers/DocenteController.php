<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

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
            'decision'            => 'required|in:aprobado,rechazado,evidencia',
            'comentario'          => 'nullable|string|max:500',
            'nota_sugerida_admin' => $request->decision === 'aprobado'
                                    ? 'required|string|min:1|max:500'
                                    : 'nullable|string|max:500',
            'evidencias'          => 'nullable|array',
            'evidencias.*'        => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($request->decision === 'rechazado' || $request->decision === 'evidencia') {
            $rules['comentario'] = 'required|string|min:10|max:500';
        }

        $request->validate($rules, [
            'comentario.required' => $request->decision === 'evidencia'
                ? 'Debes indicar qué evidencia necesitas del estudiante.'
                : 'Debes escribir una justificación para rechazar la solicitud.',
            'comentario.min'      => $request->decision === 'evidencia'
                ? 'La solicitud de evidencia debe tener al menos 10 caracteres.'
                : 'La justificación debe tener al menos 10 caracteres.',
            'nota_sugerida_admin.required'   => 'Debes indicar la nota sugerida para el administrador académico.',
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

        if ($request->decision === 'aprobado') {
            $nuevoEstado = 'pendiente_coordinador';
            $accionTexto = 'Aprobado por docente';
        } elseif ($request->decision === 'evidencia') {
            $nuevoEstado = 'requiere_evidencia';
            $accionTexto = 'Evidencia solicitada por docente';
        } else {
            $nuevoEstado = 'rechazado_docente';
            $accionTexto = 'Rechazado por docente';
        }

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

        DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->update(['estado' => $nuevoEstado]);

        $mensaje = match($request->decision) {
            'aprobado'  => 'Solicitud aprobada. Ha sido enviada al coordinador.',
            'evidencia' => 'Se ha solicitado más evidencia al estudiante.',
            default     => 'Solicitud rechazada correctamente.',
        };

        return redirect('/docente/dashboard')->with('success', $mensaje);
    }

    // =====================================================
    // EXPORTAR PDF: Genera la constancia de correccion
    // Solo para solicitudes finalizadas asignadas al docente
    // =====================================================
    public function exportarPdf($id)
    {
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('solicitudes_correccion.docente_id', Auth::id())
            ->where('solicitudes_correccion.estado', 'finalizado')
            ->select(
                'solicitudes_correccion.id', 'solicitudes_correccion.seccion',
                'solicitudes_correccion.evaluacion', 'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual', 'solicitudes_correccion.motivo',
                'solicitudes_correccion.estado', 'solicitudes_correccion.fecha_solicitud',
                'materias.nombre as materia_nombre', 'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre', 'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet', 'docentes.nombre as docente_nombre'
            )
            ->first();

        if (!$solicitud) {
            return redirect('/docente/dashboard')
                ->with('error', 'Solo puedes descargar la constancia de solicitudes finalizadas que te pertenecen.');
        }

        $historialNota = DB::table('historial_notas')
            ->where('solicitud_id', $id)->orderBy('fecha', 'desc')->first();
        if (!$historialNota) {
            return redirect('/docente/dashboard')
                ->with('error', 'No se encontró el historial de notas para generar la constancia.');
        }

        $decisionDocente = DB::table('aprobaciones')->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)->where('aprobaciones.accion', 'like', '%docente%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario', 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')->first();

        $decisionCoordinador = DB::table('aprobaciones')->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)->where('aprobaciones.accion', 'like', '%coordinador%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario', 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')->first();

        $decisionAdmin = DB::table('aprobaciones')->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)->where('aprobaciones.accion', 'like', '%admin%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario', 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')->first();

        $fechaEmision = now();
        $pdf = Pdf::loadView('pdf.constancia_correccion', compact(
            'solicitud', 'historialNota', 'decisionDocente', 'decisionCoordinador', 'decisionAdmin', 'fechaEmision'
        ))->setPaper('letter');

        return $pdf->download('Constancia_SCN-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}
