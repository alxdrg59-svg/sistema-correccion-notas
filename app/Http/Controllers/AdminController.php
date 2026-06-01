<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // =====================================================
    // DASHBOARD: Solo muestra solicitudes en pendiente_admin
    // O sea las que el coordinador ya aprobó
    // =====================================================
    public function index()
    {
        $solicitudes = DB::table('solicitudes_correccion')
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
                'materias.nombre as materia_nombre',
                'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre'
            )
            ->orderByRaw("CASE WHEN solicitudes_correccion.estado = 'pendiente_admin' THEN 0 ELSE 1 END")
            ->orderBy('solicitudes_correccion.fecha_solicitud', 'desc')
            ->get();

        $contadores = [
            'pendientes'  => $solicitudes->where('estado', 'pendiente_admin')->count(),
            'finalizadas' => $solicitudes->where('estado', 'finalizado')->count(),
            'rechazadas'  => $solicitudes->whereIn('estado', ['rechazado_docente', 'rechazado_coordinador'])->count(),
            'total'       => $solicitudes->count(),
        ];

        return view('dashboard_admin', compact('solicitudes', 'contadores'));
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

        // Evidencia del docente si subió
        $evidenciaDocente = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->where('usuario_id', $solicitud->docente_id)
            ->orderBy('fecha', 'desc')
            ->first();

        $historialNota = DB::table('historial_notas')
            ->where('solicitud_id', $id)
            ->orderBy('fecha', 'desc')
            ->first();

        return view('detalle_solicitud_admin', compact(
            'solicitud',
            'decisionDocente',
            'decisionCoordinador',
            'evidenciaDocente',
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
            'nota_nueva'  => 'required|numeric|min:0|max:10',
            'comentario'  => 'nullable|string|max:500',
        ], [
            'nota_nueva.required' => 'Debes ingresar la nota correcta.',
            'nota_nueva.numeric'  => 'La nota debe ser un número válido.',
            'nota_nueva.min'      => 'La nota no puede ser menor a 0.',
            'nota_nueva.max'      => 'La nota no puede ser mayor a 10.',
        ]);

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

        return view('gestionar_periodos', compact('periodos', 'ciclos'));
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

    // =====================================================
    // TOGGLE PERIODO: Activa o desactiva un periodo individual
    //
    // Simplemente cambia el estado del periodo entre 0 y 1.
    // Cada periodo se maneja de forma independiente: activar
    // o desactivar uno NO afecta a los demas periodos.
    //
    // Las excepciones ahora se manejan desde el formulario
    // del estudiante, no desde la activacion de periodos.
    //
    // Se llama por AJAX desde la vista gestionar_periodos,
    // pero tambien soporta peticiones normales (no-AJAX).
    //
    // En respuesta AJAX, devuelve el estado actualizado
    // de TODOS los periodos para que el JavaScript pueda
    // actualizar los badges y botones en la vista sin recargar.
    // =====================================================
    public function togglePeriodo(Request $request, $id)
    {
        $periodo = DB::table('periodos_correccion')->where('id', $id)->first();

        if (!$periodo) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Periodo no encontrado.'], 404);
            }
            return redirect('/admin/periodos')->with('error', 'Periodo no encontrado.');
        }

        // Cambiar el estado: si estaba activo (1) pasa a inactivo (0) y viceversa
        $nuevoEstado = (int)$periodo->estado === 1 ? 0 : 1;
        DB::table('periodos_correccion')->where('id', $id)->update(['estado' => $nuevoEstado]);

        // Si la peticion fue por AJAX, devolver JSON con el estado de todos los periodos
        if ($request->ajax()) {
            $periodos = DB::table('periodos_correccion')->get();
            $resultado = [];
            $hoy = now()->toDateString();
            foreach ($periodos as $p) {
                // Un periodo solo esta "activo" si estado=1 Y la fecha de hoy esta dentro del rango
                $activo = (int)$p->estado === 1 && $p->fecha_inicio <= $hoy && $p->fecha_fin >= $hoy;
                $resultado[$p->id] = [
                    'estado' => (int)$p->estado,
                    'activo' => $activo,
                ];
            }
            return response()->json(['periodos' => $resultado]);
        }

        return redirect('/admin/periodos')->with('success', 'Estado actualizado.');
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
}