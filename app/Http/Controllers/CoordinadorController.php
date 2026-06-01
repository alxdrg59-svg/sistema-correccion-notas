<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CoordinadorController extends Controller
{
    // =====================================================
    // DASHBOARD: Lista TODAS las solicitudes de las
    // carreras que pertenecen a la facultad del coordinador
    // Filtro: facultades.coordinador_id = Auth::id()
    // =====================================================
    public function index()
    {
        // Primero obtenemos la facultad del coordinador logueado
        $facultad = DB::table('facultades')
            ->where('coordinador_id', Auth::id())
            ->first();

        if (!$facultad) {
            return redirect('/login')
                ->with('error', 'No tienes una facultad asignada como coordinador.');
        }

        // Traer todas las solicitudes de carreras de su facultad
        $solicitudes = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('carreras.facultad_id', $facultad->id)
            // Solo ve las que el docente ya aprobó o las que él mismo ya procesó
            ->whereNotIn('solicitudes_correccion.estado', ['pendiente_docente', 'rechazado_docente'])
            ->select(
                'solicitudes_correccion.id',
                'solicitudes_correccion.evaluacion',
                'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual',
                'solicitudes_correccion.estado',
                'solicitudes_correccion.fecha_solicitud',
                'materias.nombre as materia_nombre',
                'carreras.nombre as carrera_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre',
                'solicitudes_correccion.es_excepcion'
            )
            ->orderBy('solicitudes_correccion.fecha_solicitud', 'desc')
            ->get();

        return view('dashboard_coordinador', compact('solicitudes', 'facultad'));
    }

    // =====================================================
    // DETALLE: Muestra la solicitud completa al coordinador
    // Incluye la decisión y evidencia del docente
    // Verifica que la solicitud pertenece a su facultad
    // =====================================================
    public function verDetalle($id)
    {
        // Obtener facultad del coordinador
        $facultad = DB::table('facultades')
            ->where('coordinador_id', Auth::id())
            ->first();

        if (!$facultad) {
            return redirect('/coordinador/dashboard')
                ->with('error', 'No tienes una facultad asignada.');
        }

        // Traer solicitud verificando que pertenece a su facultad
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('carreras.facultad_id', $facultad->id)
            ->select(
                'solicitudes_correccion.id',
                'solicitudes_correccion.seccion',
                'solicitudes_correccion.evaluacion',
                'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual',
                'solicitudes_correccion.motivo',
                'solicitudes_correccion.estado',
                'solicitudes_correccion.fecha_solicitud',
                'materias.nombre as materia_nombre',
                'carreras.nombre as carrera_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre',
                'docentes.id as docente_id',
                'solicitudes_correccion.es_excepcion'
            )
            ->first();

        if (!$solicitud) {
            return redirect('/coordinador/dashboard')
                ->with('error', 'Solicitud no encontrada o no pertenece a tu facultad.');
        }

        // Decisión del docente (la más reciente)
        $decisionDocente = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%docente%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select(
                'aprobaciones.accion',
                'aprobaciones.comentario',
                'aprobaciones.fecha',
                'usuarios.nombre as actor_nombre'
            )
            ->first();

        // Evidencias subidas por el docente
        $evidenciasDocente = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', $solicitud->docente_id)
            ->orderBy('fecha', 'desc')
            ->get();

        // Evidencias subidas por el estudiante
        $evidenciasEstudiante = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('descripcion', 'like', '%estudiante%')
            ->orderBy('fecha', 'desc')
            ->get();

        // Evidencias subidas por el coordinador
        $evidenciasCoordinador = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha', 'desc')
            ->get();

        // Decisión previa del coordinador (si ya actuó antes — para edición)
        $decisionCoordinador = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%coordinador%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select(
                'aprobaciones.accion',
                'aprobaciones.comentario',
                'aprobaciones.fecha',
                'usuarios.nombre as actor_nombre'
            )
            ->first();

        // Verificar si el admin ya actuó — si sí, bloquear edición
        $adminYaActuo = DB::table('aprobaciones')
            ->where('solicitud_id', $id)
            ->where('accion', 'like', '%admin%')
            ->exists();

        return view('detalle_solicitud_coordinador', compact(
            'solicitud',
            'decisionDocente',
            'evidenciasDocente',
            'evidenciasEstudiante',
            'evidenciasCoordinador',
            'decisionCoordinador',
            'adminYaActuo'
        ));
    }

    // =====================================================
    // PROCESAR DECISIÓN: Aprueba o rechaza
    // Si aprueba: estado → pendiente_admin
    // Si rechaza: estado → rechazado_coordinador
    // Comentario obligatorio al rechazar
    // =====================================================
    public function procesarDecision(Request $request, $id)
    {
        // Verificar que la solicitud pertenece a su facultad
        $facultad = DB::table('facultades')
            ->where('coordinador_id', Auth::id())
            ->first();

        if (!$facultad) {
            return redirect('/coordinador/dashboard')
                ->with('error', 'No tienes una facultad asignada.');
        }

        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('carreras.facultad_id', $facultad->id)
            ->select('solicitudes_correccion.id', 'solicitudes_correccion.estado')
            ->first();

        if (!$solicitud) {
            return redirect('/coordinador/dashboard')
                ->with('error', 'No tienes permiso para procesar esta solicitud.');
        }

        // Verificar que el admin no haya actuado (bloqueo de edición)
        $adminYaActuo = DB::table('aprobaciones')
            ->where('solicitud_id', $id)
            ->where('accion', 'like', '%admin%')
            ->exists();

        if ($adminYaActuo) {
            return redirect('/coordinador/solicitud/' . $id)
                ->with('error', 'El administrador ya procesó esta solicitud. No puedes modificar tu decisión.');
        }

        // Validación — comentario obligatorio al rechazar, evidencias opcionales
        $rules = [
            'decision'     => 'required|in:aprobado,rechazado',
            'comentario'   => 'nullable|string|max:500',
            'evidencias'   => 'nullable|array',
            'evidencias.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($request->decision === 'rechazado') {
            $rules['comentario'] = 'required|string|min:10|max:500';
        }

        $request->validate($rules, [
            'comentario.required' => 'Debes escribir una justificación para rechazar la solicitud.',
            'comentario.min'      => 'La justificación debe tener al menos 10 caracteres.',
            'evidencias.*.mimes'  => 'Solo se permiten archivos JPG, PNG o PDF.',
            'evidencias.*.max'    => 'Cada archivo no puede superar los 5MB.',
        ]);

        // Definir nuevo estado y texto según decisión
        if ($request->decision === 'aprobado') {
            $nuevoEstado = 'pendiente_admin';
            $accionTexto = 'Aprobado por coordinador';
        } else {
            $nuevoEstado = 'rechazado_coordinador';
            $accionTexto = 'Rechazado por coordinador';
        }

        // Borrar evidencias anteriores del coordinador si esta editando
        $evidenciasPrevias = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', Auth::id())
            ->get();
        foreach ($evidenciasPrevias as $evPrevia) {
            Storage::disk('gcs')->delete($evPrevia->archivo);
            DB::table('evidencias')->where('id', $evPrevia->id)->delete();
        }

        // Guardar nuevas evidencias del coordinador
        if ($request->hasFile('evidencias')) {
            foreach ($request->file('evidencias') as $index => $archivo) {
                if ($archivo->isValid()) {
                    $extension = $archivo->getClientOriginalExtension();
                    $nombreArchivo = 'evidencia_coordinador_' . $id . '_' . time() . '_' . $index . '.' . $extension;
                    $rutaArchivo = $archivo->storeAs('evidencias', $nombreArchivo, 'gcs');
                    DB::table('evidencias')->insert([
                        'solicitud_id' => $id,
                        'usuario_id'   => Auth::id(),
                        'archivo'      => $rutaArchivo,
                        'descripcion'  => 'Evidencia adjuntada por coordinador',
                        'fecha'        => now(),
                    ]);
                }
            }
        }

        // Registrar en aprobaciones
        DB::table('aprobaciones')->insert([
            'solicitud_id' => $id,
            'usuario_id'   => Auth::id(),
            'accion'       => $accionTexto,
            'comentario'   => $request->comentario ?? null,
            'fecha'        => now(),
        ]);

        // Actualizar estado
        DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->update(['estado' => $nuevoEstado]);

        $mensaje = $request->decision === 'aprobado'
            ? 'Solicitud aprobada. Ha sido enviada al administrador.'
            : 'Solicitud rechazada correctamente.';

        return redirect('/coordinador/dashboard')->with('success', $mensaje);
    }
}