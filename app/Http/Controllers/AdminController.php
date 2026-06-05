<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // =====================================================
    // DASHBOARD: Solo muestra solicitudes en pendiente_admin
    // O sea las que el coordinador ya aprobó
    // =====================================================
    public function index(Request $request)
    {
        $query = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->select(
                'solicitudes_correccion.id',
                'solicitudes_correccion.evaluacion',
                'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual',
                'solicitudes_correccion.estado',
                'solicitudes_correccion.fecha_solicitud',
                'solicitudes_correccion.es_excepcion',
                'facultades.id as facultad_id',
                'materias.nombre as materia_nombre',
                'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre'
            );

        if ($request->filled('facultad_id')) {
            $query->where('facultades.id', $request->facultad_id);
        }
        if ($request->filled('evaluacion')) {
            $query->where('solicitudes_correccion.evaluacion', $request->evaluacion);
        }
        if ($request->filled('ciclo')) {
            $query->where('solicitudes_correccion.ciclo', $request->ciclo);
        }
        if ($request->filled('anio')) {
            $query->whereYear('solicitudes_correccion.fecha_solicitud', $request->anio);
        }

        $solicitudes = $query
            ->orderByRaw("CASE WHEN solicitudes_correccion.estado = 'pendiente_admin' THEN 0 ELSE 1 END")
            ->orderBy('solicitudes_correccion.fecha_solicitud', 'desc')
            ->get();

        $contadores = [
            'pendientes'  => $solicitudes->where('estado', 'pendiente_admin')->count(),
            'finalizadas' => $solicitudes->where('estado', 'finalizado')->count(),
            'rechazadas'  => $solicitudes->whereIn('estado', ['rechazado_docente', 'rechazado_coordinador'])->count(),
            'total'       => $solicitudes->count(),
        ];

        $facultades = DB::table('facultades')->orderBy('nombre')->get();
        $evaluaciones = DB::table('solicitudes_correccion')->select('evaluacion')->distinct()->orderBy('evaluacion')->pluck('evaluacion');
        $ciclos = DB::table('solicitudes_correccion')->select('ciclo')->distinct()->orderBy('ciclo')->pluck('ciclo');
        $anios = DB::table('solicitudes_correccion')->selectRaw('YEAR(fecha_solicitud) as anio')->distinct()->orderBy('anio', 'desc')->pluck('anio');

        return view('admin.dashboard', compact('solicitudes', 'contadores', 'facultades', 'evaluaciones', 'ciclos', 'anios'));
    }

    // =====================================================
    // DETALLE: Muestra toda la trazabilidad de la solicitud
    // El admin ve decisión del docente y coordinador
    // y el comentario con la nota correcta sugerida
    // =====================================================
    public function verDetalle($id)
    {
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
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
                'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre',
                'docentes.id as docente_id'
            )
            ->first();

        if (!$solicitud) {
            return redirect('/admin/dashboard')
                ->with('error', 'Solicitud no encontrada.');
        }

        // Decisión del docente
        $decisionDocente = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%docente%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select(
                'aprobaciones.accion',
                'aprobaciones.comentario',
                'aprobaciones.nota_sugerida_admin',
                'aprobaciones.fecha',
                'usuarios.nombre as actor_nombre'
            )
            ->first();

        // Decisión del coordinador
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

        // Evidencias por rol
        $evidenciasEstudiante = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('descripcion', 'like', '%estudiante%')
            ->orderBy('fecha', 'desc')->get();

        $evidenciasDocente = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', $solicitud->docente_id)
            ->orderBy('fecha', 'desc')->get();

        $evidenciasCoordinador = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('descripcion', 'like', '%coordinador%')
            ->orderBy('fecha', 'desc')->get();

        $evidenciasAdmin = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('descripcion', 'like', '%admin%')
            ->orderBy('fecha', 'desc')->get();

        $historialNota = DB::table('historial_notas')
            ->where('solicitud_id', $id)
            ->orderBy('fecha', 'desc')
            ->first();

        return view('admin.detalle_solicitud', compact(
            'solicitud',
            'decisionDocente',
            'decisionCoordinador',
            'evidenciasEstudiante',
            'evidenciasDocente',
            'evidenciasCoordinador',
            'evidenciasAdmin',
            'historialNota'
        ));
    }

    // =====================================================
    // FINALIZAR: Registra la nota nueva y cierra el flujo
    // Una vez finalizado NADIE puede editar nada
    // Registra en aprobaciones + historial_notas
    // =====================================================
    public function finalizar(Request $request, $id)
    {
        // Verificar que la solicitud existe y está en pendiente_admin
        $solicitud = DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->where('estado', 'pendiente_admin')
            ->first();

        if (!$solicitud) {
            return redirect('/admin/dashboard')
                ->with('error', 'Esta solicitud no existe o ya fue procesada.');
        }

        // Validación de la nota nueva — rango 0.0 a 10
        $request->validate([
            'nota_nueva'   => 'required|numeric|min:0|max:10',
            'comentario'   => 'nullable|string|max:500',
            'evidencias'   => 'nullable|array',
            'evidencias.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'nota_nueva.required' => 'Debes ingresar la nota correcta.',
            'nota_nueva.numeric'  => 'La nota debe ser un número válido.',
            'nota_nueva.min'      => 'La nota no puede ser menor a 0.',
            'nota_nueva.max'      => 'La nota no puede ser mayor a 10.',
            'evidencias.*.mimes'  => 'Solo se permiten archivos JPG, PNG o PDF.',
            'evidencias.*.max'    => 'Cada archivo no puede superar los 5MB.',
        ]);

        // Guardar evidencias del admin
        if ($request->hasFile('evidencias')) {
            foreach ($request->file('evidencias') as $index => $archivo) {
                if ($archivo->isValid()) {
                    $extension = $archivo->getClientOriginalExtension();
                    $nombreArchivo = 'evidencia_admin_' . $id . '_' . time() . '_' . $index . '.' . $extension;
                    $rutaArchivo = $archivo->storeAs('evidencias', $nombreArchivo, 'gcs');
                    DB::table('evidencias')->insert([
                        'solicitud_id' => $id,
                        'usuario_id'   => Auth::id(),
                        'archivo'      => $rutaArchivo,
                        'descripcion'  => 'Evidencia adjuntada por admin',
                        'fecha'        => now(),
                    ]);
                }
            }
        }

        // Registrar en aprobaciones
        DB::table('aprobaciones')->insert([
            'solicitud_id' => $id,
            'usuario_id'   => Auth::id(),
            'accion'       => 'Finalizado por admin',
            'comentario'   => $request->comentario ?? null,
            'fecha'        => now(),
        ]);

        // Registrar en historial_notas
        DB::table('historial_notas')->insert([
            'solicitud_id'  => $id,
            'nota_anterior' => $solicitud->nota_actual,
            'nota_nueva'    => $request->nota_nueva,
            'usuario_id'    => Auth::id(),
            'fecha'         => now(),
        ]);

        // Actualizar estado a finalizado — nadie más puede tocar esto
        DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->update(['estado' => 'finalizado']);

        return redirect('/admin/dashboard')
            ->with('success', 'Corrección de nota finalizada exitosamente. El registro ha sido actualizado.');
    }

    public function periodos()
    {
        $this->autoGenerarCiclos();

        $periodos = DB::table('periodos_correccion')
            ->join('ciclos_academicos', 'periodos_correccion.ciclo_id', '=', 'ciclos_academicos.id')
            ->select(
                'periodos_correccion.*',
                'ciclos_academicos.nombre as ciclo_nombre'
            )
            ->orderBy('periodos_correccion.id', 'asc')
            ->get();

        $ciclos = DB::table('ciclos_academicos')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.gestionar_periodos', compact('periodos', 'ciclos'));
    }

    private function autoGenerarCiclos()
    {
        $anioActual = (int) now()->format('Y');

        $ciclos = DB::table('ciclos_academicos')->get();

        foreach ($ciclos as $ciclo) {
            $partes = explode('-', $ciclo->nombre);
            if (count($partes) !== 2) continue;

            $numero = $partes[0];
            $anioCiclo = (int) $partes[1];

            if ($anioCiclo < $anioActual) {
                $fechaInicio = $numero === '01' ? "{$anioActual}-01-01" : "{$anioActual}-07-01";
                $fechaFin    = $numero === '01' ? "{$anioActual}-06-30" : "{$anioActual}-12-31";

                DB::table('ciclos_academicos')
                    ->where('id', $ciclo->id)
                    ->update([
                        'nombre'       => "{$numero}-{$anioActual}",
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin'    => $fechaFin,
                        'estado'       => 'inactivo',
                    ]);
            }
        }
    }

    public function actualizarCiclo(Request $request, $id)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        DB::table('ciclos_academicos')
            ->where('id', $id)
            ->update([
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin'    => $request->fecha_fin,
            ]);

        return redirect('/admin/periodos')
            ->with('success', 'Fechas del ciclo académico actualizadas correctamente.');
    }

    public function actualizarPeriodo(Request $request, $id)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_inicio.required'    => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'       => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        $periodo = DB::table('periodos_correccion')->where('id', $id)->first();
        if (!$periodo) {
            return redirect('/admin/periodos')
                ->with('error', 'El periodo solicitado no existe.');
        }

        // Verificar que las nuevas fechas no se superpongan con otro periodo del mismo ciclo.
        // Se busca cualquier periodo (que no sea este mismo) cuyo rango de fechas
        // se cruce con el rango que se quiere guardar.
        $hayTraslape = DB::table('periodos_correccion')
            ->where('ciclo_id', $periodo->ciclo_id)
            ->where('id', '!=', $id)
            ->where('fecha_inicio', '<=', $request->fecha_fin)
            ->where('fecha_fin', '>=', $request->fecha_inicio)
            ->first();

        if ($hayTraslape) {
            return redirect('/admin/periodos')
                ->with('error', 'Las fechas se superponen con "' . $hayTraslape->evaluacion . '" (' .
                    \Carbon\Carbon::parse($hayTraslape->fecha_inicio)->format('d/m/Y') . ' — ' .
                    \Carbon\Carbon::parse($hayTraslape->fecha_fin)->format('d/m/Y') .
                    '). Ajuste las fechas para que no se crucen.');
        }

        DB::table('periodos_correccion')
            ->where('id', $id)
            ->update([
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin'    => $request->fecha_fin,
            ]);

        return redirect('/admin/periodos')
            ->with('success', 'Periodo "' . $periodo->evaluacion . '" actualizado correctamente.');
    }

    public function exportarPdf($id)
    {
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
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
            return redirect('/admin/dashboard')
                ->with('error', 'Solo se puede exportar PDF de solicitudes finalizadas.');
        }

        $historialNota = DB::table('historial_notas')
            ->where('solicitud_id', $id)->orderBy('fecha', 'desc')->first();

        if (!$historialNota) {
            return redirect('/admin/dashboard')
                ->with('error', 'No se encontró el historial de notas para generar la constancia.');
        }

        $decisionDocente = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%docente%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario', 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
            ->first();

        $decisionCoordinador = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%coordinador%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario', 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
            ->first();

        $decisionAdmin = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%admin%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario', 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
            ->first();

        $fechaEmision = now();

        $pdf = Pdf::loadView('pdf.constancia_correccion', compact(
            'solicitud', 'historialNota', 'decisionDocente',
            'decisionCoordinador', 'decisionAdmin', 'fechaEmision'
        ))->setPaper('letter');

        return $pdf->download('Constancia_SCN-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function estadisticas(Request $request)
    {
        $query = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id');

        if ($request->filled('evaluacion')) {
            $query->where('solicitudes_correccion.evaluacion', $request->evaluacion);
        }
        if ($request->filled('ciclo')) {
            $query->where('solicitudes_correccion.ciclo', $request->ciclo);
        }
        if ($request->filled('anio')) {
            $query->whereYear('solicitudes_correccion.fecha_solicitud', $request->anio);
        }

        $estadisticas = $query
            ->select(
                'facultades.id as facultad_id',
                'facultades.nombre as facultad_nombre',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN solicitudes_correccion.estado = 'pendiente_admin' THEN 1 ELSE 0 END) as pendientes"),
                DB::raw("SUM(CASE WHEN solicitudes_correccion.estado = 'finalizado' THEN 1 ELSE 0 END) as finalizadas"),
                DB::raw("SUM(CASE WHEN solicitudes_correccion.estado IN ('rechazado_docente', 'rechazado_coordinador') THEN 1 ELSE 0 END) as rechazadas"),
                DB::raw("SUM(CASE WHEN solicitudes_correccion.es_excepcion = 1 THEN 1 ELSE 0 END) as excepciones")
            )
            ->groupBy('facultades.id', 'facultades.nombre')
            ->orderBy('total', 'desc')
            ->get();

        $estudiantesPorFacultad = DB::table('usuarios')
            ->join('carreras', 'usuarios.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->where('usuarios.rol', 'estudiante')
            ->select('facultades.id as facultad_id', DB::raw('COUNT(*) as total_estudiantes'))
            ->groupBy('facultades.id')
            ->pluck('total_estudiantes', 'facultad_id');

        $evaluaciones = DB::table('solicitudes_correccion')->select('evaluacion')->distinct()->orderBy('evaluacion')->pluck('evaluacion');
        $ciclos = DB::table('solicitudes_correccion')->select('ciclo')->distinct()->orderBy('ciclo')->pluck('ciclo');
        $anios = DB::table('solicitudes_correccion')->selectRaw('YEAR(fecha_solicitud) as anio')->distinct()->orderBy('anio', 'desc')->pluck('anio');

        return view('admin.estadisticas', compact('estadisticas', 'estudiantesPorFacultad', 'evaluaciones', 'ciclos', 'anios'));
    }

    public function buscarEstudiante(Request $request)
    {
        $busqueda = trim($request->q);
        $estudiante = null;
        $solicitudes = collect();

        if ($busqueda) {
            $estudiante = DB::table('usuarios')
                ->leftJoin('carreras', 'usuarios.carrera_id', '=', 'carreras.id')
                ->leftJoin('facultades', 'carreras.facultad_id', '=', 'facultades.id')
                ->where('usuarios.rol', 'estudiante')
                ->where(function ($q) use ($busqueda) {
                    $q->where('usuarios.carnet', 'like', '%' . $busqueda . '%')
                      ->orWhere('usuarios.nombre', 'like', '%' . $busqueda . '%');
                })
                ->select(
                    'usuarios.id', 'usuarios.nombre', 'usuarios.carnet', 'usuarios.correo',
                    'carreras.nombre as carrera_nombre',
                    'facultades.nombre as facultad_nombre'
                )
                ->first();

            if ($estudiante) {
                $solicitudes = DB::table('solicitudes_correccion')
                    ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
                    ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
                    ->where('solicitudes_correccion.estudiante_id', $estudiante->id)
                    ->select(
                        'solicitudes_correccion.id',
                        'solicitudes_correccion.evaluacion',
                        'solicitudes_correccion.ciclo',
                        'solicitudes_correccion.nota_actual',
                        'solicitudes_correccion.estado',
                        'solicitudes_correccion.fecha_solicitud',
                        'solicitudes_correccion.es_excepcion',
                        'materias.nombre as materia_nombre',
                        'docentes.nombre as docente_nombre'
                    )
                    ->orderBy('solicitudes_correccion.fecha_solicitud', 'desc')
                    ->get();
            }
        }

        $evaluaciones = DB::table('solicitudes_correccion')->select('evaluacion')->distinct()->orderBy('evaluacion')->pluck('evaluacion');
        $ciclos = DB::table('solicitudes_correccion')->select('ciclo')->distinct()->orderBy('ciclo')->pluck('ciclo');
        $anios = DB::table('solicitudes_correccion')->selectRaw('YEAR(fecha_solicitud) as anio')->distinct()->orderBy('anio', 'desc')->pluck('anio');

        return view('admin.estadisticas', compact('estudiante', 'solicitudes', 'busqueda', 'evaluaciones', 'ciclos', 'anios'))
            ->with('estadisticas', collect())
            ->with('estudiantesPorFacultad', collect())
            ->with('tabActiva', 'busqueda');
    }
}