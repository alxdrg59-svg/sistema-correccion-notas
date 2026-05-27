<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudCorreccion; // Asegúrate de que el modelo exista
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class SolicitudController extends Controller
{
    public function index()
    {
        // Ordenadas de más reciente a más antigua (fecha_solicitud DESC)
        $solicitudes = SolicitudCorreccion::where('estudiante_id', Auth::id())
            ->orderBy('fecha_solicitud', 'desc')
            ->get();

        return view('dashboard_estudiante', compact('solicitudes'));
    }
    public function crearSolicitud()
{
    // Fecha real del servidor (para evaluar contra fecha_inicio / fecha_fin)
    $fechaHoy = now()->toDateString();

    // Periodo ACTIVO: estado = 1 Y fecha de hoy dentro del rango
    $periodoActivo = DB::table('periodos_correccion')
        ->where('estado', 1)
        ->where('fecha_inicio', '<=', $fechaHoy)
        ->where('fecha_fin', '>=', $fechaHoy)
        ->first();

    // Si ningún periodo activo cumple, no se aceptan solicitudes
    if (!$periodoActivo) {
        return redirect('/estudiante/dashboard')
            ->with('error', 'Por el momento el periodo de recepción de solicitudes para la corrección de notas se encuentra cerrado.');
    }

    // EXCEPCIÓN: dentro del mismo ciclo, también se permite reclamar la
    // evaluación INMEDIATAMENTE ANTERIOR a la activa (la de fecha_inicio
    // más reciente entre las que comenzaron antes que la actual)
    $periodoAnterior = DB::table('periodos_correccion')
        ->where('ciclo_id', $periodoActivo->ciclo_id)
        ->where('id', '!=', $periodoActivo->id)
        ->where('fecha_inicio', '<', $periodoActivo->fecha_inicio)
        ->orderBy('fecha_inicio', 'desc')
        ->first();

    //  Carrera y facultad del estudiante logueado
    $datosEstudiante = DB::table('usuarios')
        ->join('carreras', 'usuarios.carrera_id', '=', 'carreras.id')
        ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
        ->where('usuarios.id', Auth::id())
        ->select(
            'carreras.nombre as carrera_nombre',
            'facultades.nombre as facultad_nombre'
        )
        ->first();

    //  Materias del estudiante con su docente, sección y modalidad
    $materias = DB::table('asignaciones_estudiante')
        ->join('materias', 'asignaciones_estudiante.materia_id', '=', 'materias.id')
        ->join('asignaciones_docente', function($join) {
            $join->on('asignaciones_estudiante.materia_id', '=', 'asignaciones_docente.materia_id')
                ->on('asignaciones_estudiante.seccion', '=', 'asignaciones_docente.seccion');
        })
        ->join('usuarios as docentes', 'asignaciones_docente.docente_id', '=', 'docentes.id')
        ->where('asignaciones_estudiante.estudiante_id', Auth::id())
        ->select(
            'materias.id as materia_id',
            'materias.nombre as materia_nombre',
            'asignaciones_estudiante.seccion as estudiante_seccion',
            'asignaciones_estudiante.modalidad as estudiante_modalidad',
            'docentes.id as docente_id',
            'docentes.nombre as docente_nombre'
        )
        ->get();

    return view('nueva_solicitud', compact(
        'datosEstudiante',
        'materias',
        'periodoActivo',
        'periodoAnterior'
    ));
}
public function guardarSolicitud(Request $request)
{
    // 1. VALIDACIÓN
    // Validamos los campos del formulario, asegurándonos de que el estudiante haya seleccionado una materia, sección, docente, etc.
    $request->validate([
        'materia_id'  => 'required|integer',
        'docente_id'  => 'required|integer',
        'seccion'     => 'required|string',
        'nota_actual' => 'required|numeric|min:0|max:9.9',
        'periodo_id'  => 'required|integer',
        'motivo'      => 'required|string|min:10',
    ]);

    //  BUSCAR PERIODO — con guard para evitar crash si no existe
    $periodo = DB::table('periodos_correccion')
        ->where('id', $request->periodo_id)
        ->first();
    // Si no encuentra el periodo, rebotar con mensaje
    if (!$periodo) {
        return redirect('/estudiante/nueva-solicitud')
            ->with('error', 'El periodo seleccionado no es válido.');
    }

    // SEGURIDAD: el periodo_id que llega en el formulario solo puede ser
    // el activo o la evaluación inmediatamente anterior (excepción).
    // Cualquier otro valor se rechaza aquí, en el servidor, para impedir
    // que se manipule el campo oculto/select del HTML.
    $fechaHoy = now()->toDateString();

    // Periodo actualmente abierto (estado=1 y fecha de hoy dentro del rango)
    $periodoActivo = DB::table('periodos_correccion')
        ->where('estado', 1)
        ->where('fecha_inicio', '<=', $fechaHoy)
        ->where('fecha_fin', '>=', $fechaHoy)
        ->first();

    if (!$periodoActivo) {
        return redirect('/estudiante/dashboard')
            ->with('error', 'El periodo de recepción de solicitudes se encuentra cerrado.');
    }

    // Periodo inmediatamente anterior (mismo ciclo, fecha_inicio menor a la activa).
    // De todos los que cumplen, se toma el de fecha_inicio MÁS RECIENTE.
    $periodoAnterior = DB::table('periodos_correccion')
        ->where('ciclo_id', $periodoActivo->ciclo_id)
        ->where('id', '!=', $periodoActivo->id)
        ->where('fecha_inicio', '<', $periodoActivo->fecha_inicio)
        ->orderBy('fecha_inicio', 'desc')
        ->first();

    // Conjunto de ids permitidos. Si no hay anterior, solo se admite el activo.
    $idsPermitidos = [$periodoActivo->id];
    if ($periodoAnterior) {
        $idsPermitidos[] = $periodoAnterior->id;
    }

    // Comparación final: si el periodo enviado no está en la lista permitida, rebota.
    if (!in_array($periodo->id, $idsPermitidos)) {
        return redirect('/estudiante/nueva-solicitud')
            ->with('error', 'Solo puedes reclamar la evaluación activa o la inmediatamente anterior (excepción).');
    }

    //  VERIFICAR DUPLICADO solo bloquea si hay una activa (no rechazada)
    $yaExiste = DB::table('solicitudes_correccion')
        ->where('estudiante_id', Auth::id())
        ->where('materia_id', $request->materia_id)
        ->where('evaluacion', $periodo->evaluacion)
        ->where('ciclo_id', $periodo->ciclo_id)
        ->whereNotIn('estado', ['rechazado_docente', 'rechazado_coordinador'])
        ->exists();

    if ($yaExiste) {
    return redirect('/estudiante/nueva-solicitud')
        ->with('error', 'Ya tienes una solicitud activa para esta materia y evaluación. Puedes enviar una nueva solo si la anterior fue rechazada.');
}
    //  BUSCAR CICLO — con guard
    
    $cicloData = DB::table('ciclos_academicos')
        ->where('id', $periodo->ciclo_id)
        ->first();

    if (!$cicloData) {
        return redirect('/estudiante/nueva-solicitud')
            ->with('error', 'No se encontró el ciclo académico asociado.');
    }

    // INSERT — tabla y estado corregidos
    DB::table('solicitudes_correccion')->insert([
        'estudiante_id'   => Auth::id(),
        'materia_id'      => $request->materia_id,
        'docente_id'      => $request->docente_id,
        'seccion'         => $request->seccion,
        'nota_actual'     => $request->nota_actual,
        'motivo'          => $request->motivo,
        'evaluacion'      => $periodo->evaluacion,
        'ciclo_id'        => $periodo->ciclo_id,
        'ciclo'           => $cicloData->nombre,
        'estado'          => 'pendiente_docente',
        'fecha_solicitud' => now(),
    ]);

    // 6. REDIRECCIÓN
    return redirect('/estudiante/dashboard')
        ->with('success', '¡Tu solicitud ha sido enviada al docente con éxito!');
}
public function verDetalle($id)
{
    // 1. Traer la solicitud — verificar que pertenece al estudiante logueado
    $solicitud = DB::table('solicitudes_correccion')
        ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
        ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
        ->where('solicitudes_correccion.id', $id)
        ->where('solicitudes_correccion.estudiante_id', Auth::id()) // Seguridad: solo SUS solicitudes
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
            'docentes.nombre as docente_nombre'
        )
        ->first();

    // Si no existe o no le pertenece, rebotar
    if (!$solicitud) {
        return redirect('/estudiante/dashboard')
            ->with('error', 'Solicitud no encontrada o no tienes permiso para verla.');
    }

    // 2. Traer TODO el historial de aprobaciones de esta solicitud (ordenado por fecha)
    $aprobaciones = DB::table('aprobaciones')
        ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
        ->where('aprobaciones.solicitud_id', $id)
        ->select(
            'aprobaciones.id',
            'aprobaciones.accion',
            'aprobaciones.comentario',
            'aprobaciones.fecha',
            'usuarios.nombre as actor_nombre',
            'usuarios.rol as actor_rol'
        )
        ->orderBy('aprobaciones.fecha', 'asc')
        ->get();

    // 3. Para el timeline, necesitamos la ÚLTIMA acción de cada fase
    //    Usamos LIKE porque el campo accion es texto libre ("Aprobado por docente", etc.)
    $accionDocente      = $aprobaciones->filter(fn($a) => stripos($a->accion, 'docente') !== false)->last();
    $accionCoordinador  = $aprobaciones->filter(fn($a) => stripos($a->accion, 'coordinador') !== false)->last();
    $accionAdmin        = $aprobaciones->filter(fn($a) => stripos($a->accion, 'admin') !== false)->last();

    // 4. Historial de nota (solo existirá si ya fue finalizado)
    $historialNota = DB::table('historial_notas')
        ->where('solicitud_id', $id)
        ->orderBy('fecha', 'desc')
        ->first();

    return view('detalle_solicitud', compact(
        'solicitud',
        'aprobaciones',
        'accionDocente',
        'accionCoordinador',
        'accionAdmin',
        'historialNota'
    ));
}

// =====================================================
// EXPORTAR PDF (lado estudiante)
// ------------------------------------------------------
// Permite al estudiante descargar su constancia de
// corrección de nota únicamente cuando:
//   1) La solicitud le pertenece (estudiante_id = Auth::id())
//   2) La solicitud está finalizada
// El filtro en el WHERE garantiza que nadie pueda descargar
// la constancia de otro estudiante manipulando el id en la URL.
// =====================================================
public function exportarPdf($id)
{
    // Solicitud: joins idénticos a verDetalle del admin (carrera/facultad/docente),
    // pero con doble filtro: dueño + estado finalizado.
    $solicitud = DB::table('solicitudes_correccion')
        ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
        ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
        ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
        ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
        ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
        ->where('solicitudes_correccion.id', $id)
        ->where('solicitudes_correccion.estudiante_id', Auth::id()) // dueño
        ->where('solicitudes_correccion.estado', 'finalizado')      // solo aprobadas
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
            'docentes.nombre as docente_nombre'
        )
        ->first();

    if (!$solicitud) {
        return redirect('/estudiante/dashboard')
            ->with('error', 'Solo puedes descargar la constancia de tus solicitudes finalizadas.');
    }

    // Historial de la nota (anterior y nueva)
    $historialNota = DB::table('historial_notas')
        ->where('solicitud_id', $id)
        ->orderBy('fecha', 'desc')
        ->first();

    if (!$historialNota) {
        return redirect('/estudiante/dashboard')
            ->with('error', 'No se encontró el historial de notas para generar la constancia.');
    }

    // Decisiones por etapa (mismo patrón que el AdminController::exportarPdf)
    $decisionDocente = DB::table('aprobaciones')
        ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
        ->where('aprobaciones.solicitud_id', $id)
        ->where('aprobaciones.accion', 'like', '%docente%')
        ->orderBy('aprobaciones.fecha', 'desc')
        ->select('aprobaciones.accion', 'aprobaciones.comentario',
                 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
        ->first();

    $decisionCoordinador = DB::table('aprobaciones')
        ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
        ->where('aprobaciones.solicitud_id', $id)
        ->where('aprobaciones.accion', 'like', '%coordinador%')
        ->orderBy('aprobaciones.fecha', 'desc')
        ->select('aprobaciones.accion', 'aprobaciones.comentario',
                 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
        ->first();

    $decisionAdmin = DB::table('aprobaciones')
        ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
        ->where('aprobaciones.solicitud_id', $id)
        ->where('aprobaciones.accion', 'like', '%admin%')
        ->orderBy('aprobaciones.fecha', 'desc')
        ->select('aprobaciones.accion', 'aprobaciones.comentario',
                 'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
        ->first();

    $fechaEmision = now();

    $pdf = Pdf::loadView('pdf.constancia_correccion', compact(
        'solicitud',
        'historialNota',
        'decisionDocente',
        'decisionCoordinador',
        'decisionAdmin',
        'fechaEmision'
    ))->setPaper('letter');

    $nombreArchivo = 'Constancia_SCN-'
        . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT)
        . '.pdf';

    return $pdf->download($nombreArchivo);
}
}