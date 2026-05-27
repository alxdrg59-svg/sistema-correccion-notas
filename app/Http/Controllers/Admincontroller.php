<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // =====================================================
    // DASHBOARD: Muestra TODAS las solicitudes
    //   - Pendientes de admin (acción requerida)  → mostradas primero
    //   - Finalizadas (aprobadas) y rechazadas en cualquier nivel → en seguida
    //
    // Orden:
    //   1) pendiente_admin   (urgente)
    //   2) Por fecha_solicitud DESC (más reciente primero)
    // =====================================================
    public function index()
    {
        // Consulta para obtener solo las solicitudes que están en estado 'pendiente_admin'
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
                'materias.nombre as materia_nombre',
                'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.carnet as estudiante_carnet',
                'docentes.nombre as docente_nombre'
            )
            // Las que están pendientes para el admin suben al inicio de la tabla,
            // el resto baja. Dentro de cada grupo se ordena por fecha más reciente.
            ->orderByRaw("CASE WHEN solicitudes_correccion.estado = 'pendiente_admin' THEN 0 ELSE 1 END")
            ->orderBy('solicitudes_correccion.fecha_solicitud', 'desc')
            ->get();

        // Contadores que alimentan las tarjetas resumen del dashboard.
        // Se calculan sobre la colección ya obtenida para no hacer queries extra.
        $contadores = [
            'pendientes' => $solicitudes->where('estado', 'pendiente_admin')->count(),
            'finalizadas'=> $solicitudes->where('estado', 'finalizado')->count(),
            'rechazadas' => $solicitudes->whereIn('estado', [
                                'rechazado_docente',
                                'rechazado_coordinador',
                            ])->count(),
            'total'      => $solicitudes->count(),
        ];

        return view('dashboard_admin', compact('solicitudes', 'contadores'));
    }


    // DETALLE: Muestra toda la trazabilidad de la solicitud
    // El admin ve decisión del docente y coordinador
    // y el comentario con la nota correcta sugerida

    public function verDetalle($id)
    {
        // El admin puede ver el detalle de cualquier solicitud (pendiente, finalizada,
        // rechazada). El formulario de aplicar corrección solo se renderiza si el estado
        // es pendiente_admin — eso lo controla la vista.
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

        // Historial de la nota (si ya se finalizó)
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

    // =====================================================
    // GESTIONAR PERIODOS DE CORRECCIÓN
    // Lista todos los periodos registrados junto con su ciclo
    // académico. La vista permite editar fechas y estado de
    // cada uno desde una sola pantalla.
    // =====================================================
    public function periodos()
    {
        // Trae cada periodo con el nombre del ciclo al que pertenece.
        // Ordenado por fecha_inicio DESC para que los más recientes salgan arriba.
        $periodos = DB::table('periodos_correccion')
            ->join('ciclos_academicos', 'periodos_correccion.ciclo_id', '=', 'ciclos_academicos.id')
            ->select(
                'periodos_correccion.id',
                'periodos_correccion.evaluacion',
                'periodos_correccion.fecha_inicio',
                'periodos_correccion.fecha_fin',
                'periodos_correccion.estado',
                'ciclos_academicos.nombre as ciclo_nombre'
            )
            ->orderBy('periodos_correccion.fecha_inicio', 'desc')
            ->get();

        return view('gestionar_periodos', compact('periodos'));
    }

    // =====================================================
    // ACTUALIZAR PERIODO DE CORRECCIÓN
    // Recibe nuevas fechas y estado para un periodo concreto
    // y los guarda en la base de datos.
    // =====================================================
    public function actualizarPeriodo(Request $request, $id)
    {
        // Reglas de validación:
        //   - fecha_inicio: obligatoria y con formato de fecha válido
        //   - fecha_fin:    obligatoria, fecha válida y posterior o igual a fecha_inicio
        //   - estado:       solo se aceptan 0 (cerrado) o 1 (abierto)
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'estado'       => 'required|in:0,1',
        ], [
            'fecha_inicio.required'      => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required'         => 'La fecha de fin es obligatoria.',
            'fecha_fin.after_or_equal'   => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        // Confirma que el periodo a editar realmente exista en la BD
        $periodo = DB::table('periodos_correccion')->where('id', $id)->first();
        if (!$periodo) {
            return redirect('/admin/periodos')
                ->with('error', 'El periodo solicitado no existe.');
        }

        // Sobrescribe los tres campos editables (las demás columnas no se tocan)
        DB::table('periodos_correccion')
            ->where('id', $id)
            ->update([
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin'    => $request->fecha_fin,
                'estado'       => (int) $request->estado,
            ]);

        return redirect('/admin/periodos')
            ->with('success', 'Periodo "' . $periodo->evaluacion . '" actualizado correctamente.');
    }

    // =====================================================
    // EXPORTAR PDF: Constancia oficial de Corrección de Nota
    // ------------------------------------------------------
    // Solo se permite emitir el PDF cuando la solicitud ya
    // está FINALIZADA (estado = 'finalizado'). De lo contrario
    // se rebota al admin a su dashboard con un mensaje de error,
    // porque una corrección no concluida no tiene constancia.
    //
    // Reúne toda la información necesaria para la plantilla
    // (resources/views/pdf/constancia_correccion.blade.php):
    //   - Datos del estudiante (carnet, carrera, facultad)
    //   - Datos de la materia (sección, docente, ciclo)
    //   - Historial de la nota (anterior y nueva)
    //   - Decisiones de docente, coordinador y admin con su autor
    // =====================================================
    public function exportarPdf($id)
    {
        // Solicitud completa con joins de materia / carrera / facultad / estudiante / docente
        // Reutiliza el mismo SELECT que verDetalle pero además filtra por estado finalizado.
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('solicitudes_correccion.estado', 'finalizado')
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

        // Si la solicitud no existe o aún no se ha finalizado, no se emite PDF.
        if (!$solicitud) {
            return redirect('/admin/dashboard')
                ->with('error', 'Solo se puede exportar PDF de solicitudes finalizadas.');
        }

        // Historial de la nota (debe existir porque la solicitud está finalizada)
        $historialNota = DB::table('historial_notas')
            ->where('solicitud_id', $id)
            ->orderBy('fecha', 'desc')
            ->first();

        // Si por inconsistencia de datos no hay historial, evita un crash al renderizar.
        if (!$historialNota) {
            return redirect('/admin/dashboard')
                ->with('error', 'No se encontró el historial de notas para generar la constancia.');
        }

        // Última decisión del docente (aprobado/rechazado por docente)
        $decisionDocente = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%docente%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario',
                    'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
            ->first();

        // Última decisión del coordinador
        $decisionCoordinador = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%coordinador%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario',
                    'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
            ->first();

        // Acción de cierre del admin (la que finaliza el flujo)
        $decisionAdmin = DB::table('aprobaciones')
            ->join('usuarios', 'aprobaciones.usuario_id', '=', 'usuarios.id')
            ->where('aprobaciones.solicitud_id', $id)
            ->where('aprobaciones.accion', 'like', '%admin%')
            ->orderBy('aprobaciones.fecha', 'desc')
            ->select('aprobaciones.accion', 'aprobaciones.comentario',
                    'aprobaciones.fecha', 'usuarios.nombre as actor_nombre')
            ->first();

        // Timbre de emisión (no se guarda en BD; solo se imprime en el documento)
        $fechaEmision = now();

        // Genera el PDF con la plantilla y lo devuelve como descarga.
        // El nombre del archivo se compone con el folio único para fácil archivo.
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