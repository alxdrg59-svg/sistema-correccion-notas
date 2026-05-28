<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocenteController extends Controller
{
    // DASHBOARD Lista todas las solicitudes que le
    // pertenecen al docente logueado (por docente_id)
    // Solo muestra las que están en pendiente_docente
    // o que él ya procesó para historial propio
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

        return view('dashboard_docente', compact('solicitudes'));
    } 
    // DETALLE: Muestra la solicitud completa al docente
    // Verifica que la solicitud le pertenece (seguridad)
    // También carga si ya existe una decisión previa suya
    // para saber si puede editar o no su revisión
    public function verDetalle($id)
    {
        // Traer solicitud verificando que el docente logueado es el dueño
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

        // Si no existe o no le pertenece, rebotar
        if (!$solicitud) {
            return redirect('/docente/dashboard')
                ->with('error', 'Solicitud no encontrada o no tienes permiso para verla.');
        }

        // Revisar si el coordinador ya tomó una decisión
        // Si ya lo hizo, el docente NO puede editar su revisión
        $coordinadorYaActuo = DB::table('aprobaciones')
            ->where('solicitud_id', $id)
            ->where(function($q) {
                $q->where('accion', 'like', '%coordinador%');
            })
            ->exists();

        // Traer la última decisión del docente sobre esta solicitud (si existe)
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

        // Traer evidencia subida por el docente si existe
        $evidencia = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha', 'desc')
            ->first();

        // Traer evidencia subida por el estudiante si existe
        $evidenciaEstudiante = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('descripcion', 'like', '%estudiante%')
            ->orderBy('fecha', 'desc')
            ->first();

        return view('detalle_solicitud_docente', compact(
            'solicitud',
            'coordinadorYaActuo',
            'decisionDocente',
            'evidencia',
            'evidenciaEstudiante'
        ));
    } 
    // PROCESAR DECISIÓN: Aprueba o rechaza la solicitud
    // Reglas:
    //   - Comentario obligatorio si rechaza
    //   - Evidencia opcional (imagen/PDF máx 5MB)
    //   - Si aprueba: estado → pendiente_coordinador
    //   - Si rechaza: estado → rechazado_docente
    //   - Guarda registro en tabla aprobaciones
    public function procesarDecision(Request $request, $id)
    {
        // Verificar que la solicitud le pertenece al docente logueado
        $solicitud = DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->where('docente_id', Auth::id())
            ->first();

        if (!$solicitud) {
            return redirect('/docente/dashboard')
                ->with('error', 'No tienes permiso para procesar esta solicitud.');
        }

        // Verificar que el coordinador no haya actuado aún (bloqueo de edición)
        $coordinadorYaActuo = DB::table('aprobaciones')
            ->where('solicitud_id', $id)
            ->where('accion', 'like', '%coordinador%')
            ->exists();

        if ($coordinadorYaActuo) {
            return redirect('/docente/solicitud/' . $id)
                ->with('error', 'El coordinador ya evaluó esta solicitud. No puedes modificar tu decisión.');
        }

        // Validación base
        // decision: aprobado o rechazado
        $rules = [
            'decision'            => 'required|in:aprobado,rechazado',
            'comentario'          => 'nullable|string|max:500',
            'nota_sugerida_admin' => $request->decision === 'aprobado'
                                    ? 'required|string|min:1|max:500'
                                    : 'nullable|string|max:500',
            'evidencia'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // Comentario obligatorio si rechaza
        if ($request->decision === 'rechazado') {
            $rules['comentario'] = 'required|string|min:10|max:500';
        }
        // Validar con mensajes personalizados
        $request->validate($rules, [
            'comentario.required' => 'Debes escribir una justificación para rechazar la solicitud.',
            'comentario.min'      => 'La justificación debe tener al menos 10 caracteres.',
            'nota_sugerida_admin.required'   => 'Debes indicar la nota sugerida para el administrador.',
            'nota_sugerida_admin.min'      => 'La nota sugerida debe tener al menos 1 caracter.',
            'evidencia.mimes'     => 'Solo se permiten archivos JPG, PNG o PDF.',
            'evidencia.max'       => 'El archivo no puede superar los 5MB.',
        ]);

        // LIMPIAR EVIDENCIA PREVIA si el docente está editando su decisión
        $evidenciaPrevia = DB::table('evidencias')
        ->where('solicitud_id', $id)
        ->where('usuario_id', Auth::id())
        ->first();

        if ($evidenciaPrevia) {
        // Borrar archivo físico del storage
        Storage::disk('gcs')->delete($evidenciaPrevia->archivo);
        // Borrar registro de la BD
        DB::table('evidencias')->where('id', $evidenciaPrevia->id)->delete();
}
        // Manejo del archivo de evidencia (opcional)
        $rutaArchivo = null;
        // Si se subió un archivo, lo guardamos y registramos en la tabla evidencias
        if ($request->hasFile('evidencia') && $request->file('evidencia')->isValid()) {
            // Formato: evidencias/evidencia_docente_{solicitud_id}_{timestamp}.{ext}
            $extension   = $request->file('evidencia')->getClientOriginalExtension();
            $nombreArchivo = 'evidencia_docente_' . $id . '_' . time() . '.' . $extension;
            // Guardar el archivo en el disco público (storage/app/public/evidencias)
            $rutaArchivo   = $request->file('evidencia')->storeAs('evidencias', $nombreArchivo, 'gcs');

            // Guardar en tabla evidencias
            DB::table('evidencias')->insert([
                'solicitud_id' => $id,
                'usuario_id'   => Auth::id(),
                'archivo'      => $rutaArchivo,
                'descripcion'  => 'Evidencia adjuntada por docente',
                'fecha'        => now(),
            ]);
        }

        // Definir nuevo estado y texto de acción según la decisión
        if ($request->decision === 'aprobado') {
            $nuevoEstado = 'pendiente_coordinador';
            $accionTexto = 'Aprobado por docente';
        } else {
            $nuevoEstado = 'rechazado_docente';
            $accionTexto = 'Rechazado por docente';
        }

        // Registrar en tabla aprobaciones
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

        // Actualizar estado en solicitudes_correccion
        DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->update(['estado' => $nuevoEstado]);

        $mensaje = $request->decision === 'aprobado'
            ? 'Solicitud aprobada. Ha sido enviada al coordinador de facultad.'
            : 'Solicitud rechazada correctamente.';

        return redirect('/docente/dashboard')->with('success', $mensaje);
    }

    public function excepciones()
    {
        $fechaHoy = now()->toDateString();

        $periodoActivo = DB::table('periodos_correccion')
            ->where('estado', 1)
            ->where('fecha_inicio', '<=', $fechaHoy)
            ->where('fecha_fin', '>=', $fechaHoy)
            ->first();

        $evaluacionAnterior = null;
        if ($periodoActivo) {
            $numActual = (int) filter_var($periodoActivo->evaluacion, FILTER_SANITIZE_NUMBER_INT);
            if ($numActual > 1) {
                $evaluacionAnterior = 'Evaluacion ' . ($numActual - 1);
            }
        }

        if (!$evaluacionAnterior) {
            return redirect('/docente/dashboard')
                ->with('error', 'No se puede crear una excepción: no hay un periodo activo o es la primera evaluación.');
        }

        $materias = DB::table('asignaciones_docente')
            ->join('materias', 'asignaciones_docente.materia_id', '=', 'materias.id')
            ->where('asignaciones_docente.docente_id', Auth::id())
            ->where('asignaciones_docente.estado', 'activa')
            ->select(
                'materias.id as materia_id',
                'materias.nombre as materia_nombre',
                'asignaciones_docente.seccion',
                'asignaciones_docente.modalidad',
                'asignaciones_docente.ciclo_id',
                'asignaciones_docente.ciclo'
            )
            ->get();

        $estudiantesPorMateria = [];
        foreach ($materias as $materia) {
            $estudiantes = DB::table('asignaciones_estudiante')
                ->join('usuarios', 'asignaciones_estudiante.estudiante_id', '=', 'usuarios.id')
                ->where('asignaciones_estudiante.materia_id', $materia->materia_id)
                ->where('asignaciones_estudiante.seccion', $materia->seccion)
                ->where('asignaciones_estudiante.ciclo_id', $materia->ciclo_id)
                ->select(
                    'usuarios.id as estudiante_id',
                    'usuarios.nombre as estudiante_nombre',
                    'usuarios.carnet as estudiante_carnet'
                )
                ->get();

            $key = $materia->materia_id . '_' . $materia->seccion;
            $estudiantesPorMateria[$key] = $estudiantes;
        }

        return view('excepciones_docente', compact('materias', 'estudiantesPorMateria', 'evaluacionAnterior'));
    }

    public function guardarExcepcion(Request $request)
    {
        $request->validate([
            'materia_id'     => 'required|integer',
            'estudiante_id'  => 'required|integer',
            'seccion'        => 'required|string',
            'evaluacion'     => 'required|string|in:Evaluacion 1,Evaluacion 2,Evaluacion 3,Evaluacion 4,Evaluacion 5',
            'nota_actual'    => 'required|numeric|min:0|max:10',
            'motivo'         => 'required|string|min:10',
            'justificacion'  => 'required|string|min:10',
            'evidencia'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'materia_id.required'    => 'Debe seleccionar una materia.',
            'estudiante_id.required' => 'Debe seleccionar un estudiante.',
            'seccion.required'       => 'Debe seleccionar una materia para asignar la sección.',
            'evaluacion.required'    => 'La evaluación es obligatoria.',
            'nota_actual.required'   => 'Debe ingresar la nota.',
            'nota_actual.numeric'    => 'La nota debe ser un número válido.',
            'nota_actual.min'        => 'La nota no puede ser menor a 0.',
            'nota_actual.max'        => 'La nota no puede ser mayor a 10.',
            'motivo.required'        => 'Debe ingresar el motivo del reclamo.',
            'motivo.min'             => 'El motivo debe tener al menos 10 caracteres.',
            'justificacion.required' => 'Debe justificar por qué se solicita la excepción.',
            'justificacion.min'      => 'La justificación debe tener al menos 10 caracteres.',
            'evidencia.mimes'        => 'Solo se permiten archivos JPG, PNG o PDF.',
            'evidencia.max'          => 'El archivo no puede superar los 5MB.',
        ]);

        $asignacion = DB::table('asignaciones_docente')
            ->where('docente_id', Auth::id())
            ->where('materia_id', $request->materia_id)
            ->where('seccion', $request->seccion)
            ->first();

        if (!$asignacion) {
            return redirect('/docente/excepciones')
                ->with('error', 'No tienes asignación para esta materia y sección.');
        }

        $yaExiste = DB::table('solicitudes_correccion')
            ->where('estudiante_id', $request->estudiante_id)
            ->where('materia_id', $request->materia_id)
            ->where('docente_id', Auth::id())
            ->where('evaluacion', $request->evaluacion)
            ->where('ciclo_id', $asignacion->ciclo_id)
            ->whereNotIn('estado', ['rechazado_docente', 'rechazado_coordinador'])
            ->exists();

        if ($yaExiste) {
            return redirect('/docente/excepciones')
                ->with('error', 'Ya existe una solicitud activa para este estudiante, materia y evaluación.');
        }

        $cicloData = DB::table('ciclos_academicos')
            ->where('id', $asignacion->ciclo_id)
            ->first();

        $solicitudId = DB::table('solicitudes_correccion')->insertGetId([
            'estudiante_id'   => $request->estudiante_id,
            'materia_id'      => $request->materia_id,
            'docente_id'      => Auth::id(),
            'seccion'         => $request->seccion,
            'nota_actual'     => $request->nota_actual,
            'motivo'          => $request->motivo,
            'evaluacion'      => $request->evaluacion,
            'ciclo_id'        => $asignacion->ciclo_id,
            'ciclo'           => $cicloData->nombre ?? $asignacion->ciclo,
            'estado'          => 'pendiente_coordinador',
            'es_excepcion'    => 1,
            'fecha_solicitud' => now(),
        ]);

        if ($request->hasFile('evidencia') && $request->file('evidencia')->isValid()) {
            $extension = $request->file('evidencia')->getClientOriginalExtension();
            $nombreArchivo = 'evidencia_excepcion_' . $solicitudId . '_' . time() . '.' . $extension;
            $rutaArchivo = $request->file('evidencia')->storeAs('evidencias', $nombreArchivo, 'gcs');

            DB::table('evidencias')->insert([
                'solicitud_id' => $solicitudId,
                'usuario_id'   => Auth::id(),
                'archivo'      => $rutaArchivo,
                'descripcion'  => 'Evidencia de excepción adjuntada por docente',
                'fecha'        => now(),
            ]);
        }

        DB::table('aprobaciones')->insert([
            'solicitud_id' => $solicitudId,
            'usuario_id'   => Auth::id(),
            'accion'       => 'Aprobado por docente (excepción)',
            'comentario'   => $request->justificacion,
            'fecha'        => now(),
        ]);

        DB::table('bitacora')->insert([
            'usuario_id' => Auth::id(),
            'accion'     => 'Excepción creada por docente',
            'detalle'    => 'Solicitud de excepción #' . $solicitudId . ' creada para estudiante ID ' . $request->estudiante_id,
            'fecha'      => now(),
        ]);

        return redirect('/docente/dashboard')
            ->with('success', 'Solicitud de excepción creada exitosamente. Ha sido enviada al coordinador.');
    }
}