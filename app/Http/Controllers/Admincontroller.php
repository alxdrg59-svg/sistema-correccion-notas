<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 
    // DASHBOARD: Solo muestra solicitudes en pendiente_admin
    // O sea las que el coordinador ya aprobó
    // 
    public function index()
    {
        // Consulta para obtener solo las solicitudes que están en estado 'pendiente_admin'
        $solicitudes = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.estado', 'pendiente_admin')
            ->select(
                'solicitudes_correccion.id',
                'solicitudes_correccion.evaluacion',
                'solicitudes_correccion.ciclo',
                'solicitudes_correccion.nota_actual',
                'solicitudes_correccion.estado',
                'solicitudes_correccion.fecha_solicitud',
                'materias.nombre as materia_nombre',
                'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre'
            )
            ->orderBy('solicitudes_correccion.fecha_solicitud', 'asc') // Las más antiguas primero
            ->get();

        return view('dashboard_admin', compact('solicitudes'));
    }


    // DETALLE: Muestra toda la trazabilidad de la solicitud
    // El admin ve decisión del docente y coordinador
    // y el comentario con la nota correcta sugerida

    public function verDetalle($id)
    {
        // Verificar que la solicitud existe y está en pendiente_admin
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('solicitudes_correccion.estado', 'pendiente_admin')
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
                'facultades.nombre as facultad_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre',
                'docentes.id as docente_id'
            )
            ->first();
    
        if (!$solicitud) {
            return redirect('/admin/dashboard')
                ->with('error', 'Solicitud no encontrada o ya fue procesada.');
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

        return view('detalle_solicitud_admin', compact(
            'solicitud',
            'decisionDocente',
            'decisionCoordinador',
            'evidenciaDocente'
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
}