<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudCorreccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


// =====================================================
// CONTROLADOR DE SOLICITUDES DEL ESTUDIANTE
// Maneja todo lo que el estudiante puede hacer:
//   - Ver su lista de solicitudes (dashboard)
//   - Crear una nueva solicitud (normal o por excepcion)
//   - Ver el detalle de una solicitud especifica
//   - Descargar la constancia en PDF (solo si fue finalizada)
// =====================================================
class SolicitudController extends Controller
{
    // =====================================================
    // INDEX: Muestra el dashboard del estudiante
    // Trae todas las solicitudes del estudiante logueado,
    // ordenadas de la mas reciente a la mas antigua.
    // =====================================================
    public function index()
    {
        $solicitudes = SolicitudCorreccion::where('estudiante_id', Auth::id())
            ->orderBy('fecha_solicitud', 'desc')
            ->get();

        return view('dashboard_estudiante', compact('solicitudes'));
    }

    // =====================================================
    // CREAR SOLICITUD: Muestra el formulario de nueva solicitud
    //
    // Tiene dos modos de funcionamiento:
    //
    // 1) MODO NORMAL: Si existe un periodo activo (la fecha
    //    de hoy esta dentro de fecha_inicio y fecha_fin),
    //    el estudiante puede crear una solicitud normal.
    //    Tambien puede marcar el checkbox de excepcion para
    //    solicitar correccion de la evaluacion anterior.
    //
    // 2) MODO EXCEPCION FORZADO: Si NO hay ningun periodo activo
    //    (todos los periodos ya vencieron), se busca el periodo
    //    mas reciente que ya termino. El formulario se abre
    //    en modo excepcion obligatorio: el checkbox esta marcado
    //    y no se puede desmarcar, la evidencia es obligatoria.
    //
    // Si no hay ningun periodo (ni activo ni vencido), el
    // estudiante es redirigido al dashboard con un error.
    // =====================================================
    public function crearSolicitud()
    {
        $fechaHoy = now()->toDateString();

        // Buscar un periodo cuyo rango de fechas incluya el dia de hoy.
        // No se usa el campo 'estado' — el sistema determina el periodo
        // activo automaticamente segun las fechas configuradas por el admin.
        // Se ordena por fecha_inicio descendente para que si hay traslape
        // de fechas, se tome la evaluacion mas reciente.
        $periodoActivo = DB::table('periodos_correccion')
            ->where('fecha_inicio', '<=', $fechaHoy)
            ->where('fecha_fin', '>=', $fechaHoy)
            ->orderBy('fecha_inicio', 'desc')
            ->first();

        // Estas variables se envian a la vista para controlar el modo del formulario
        $modoExcepcion = false;
        $evaluacionAnterior = null;

        if ($periodoActivo) {
            // MODO NORMAL: Hay un periodo activo.
            // Calculamos la evaluacion anterior para el dropdown de excepcion.
            // Ejemplo: si el periodo activo es "Evaluacion 3", la anterior es "Evaluacion 2".
            // Si es "Evaluacion 1", no hay evaluacion anterior (el checkbox no se muestra).
            $numActual = (int) filter_var($periodoActivo->evaluacion, FILTER_SANITIZE_NUMBER_INT);
            if ($numActual > 1) {
                $evaluacionAnterior = 'Evaluacion ' . ($numActual - 1);
            }
        } else {
            // MODO EXCEPCION FORZADO: No hay periodo activo.
            // Buscamos el periodo mas reciente que ya vencio (fecha_fin < hoy)
            $periodoActivo = DB::table('periodos_correccion')
                ->where('fecha_fin', '<', $fechaHoy)
                ->orderBy('fecha_fin', 'desc')
                ->first();

            // Si no existe ningun periodo en la base de datos, no se puede hacer nada
            if (!$periodoActivo) {
                return redirect('/estudiante/dashboard')
                    ->with('error', 'No hay periodos de corrección disponibles.');
            }

            // Activar modo excepcion: la evaluacion del periodo vencido es la que se usara
            $modoExcepcion = true;
            $evaluacionAnterior = $periodoActivo->evaluacion;
        }

        // Traer los datos de carrera y facultad del estudiante para mostrar en el formulario
        $datosEstudiante = DB::table('usuarios')
            ->join('carreras', 'usuarios.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->where('usuarios.id', Auth::id())
            ->select(
                'carreras.nombre as carrera_nombre',
                'facultades.nombre as facultad_nombre'
            )
            ->first();

        // Traer las materias del estudiante junto con su docente asignado.
        // Se hace un JOIN entre asignaciones_estudiante, materias, asignaciones_docente y usuarios.
        // Esto permite que al seleccionar una materia, se autocompleten la seccion y el docente.
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

        // Enviar todo a la vista: datos del estudiante, materias, periodo,
        // si estamos en modo excepcion, y la evaluacion anterior disponible
        return view('nueva_solicitud', compact(
            'datosEstudiante',
            'materias',
            'periodoActivo',
            'modoExcepcion',
            'evaluacionAnterior'
        ));
    }

    // =====================================================
    // GUARDAR SOLICITUD: Procesa el formulario enviado
    //
    // Pasos que sigue:
    //   1. Detectar si es una solicitud de excepcion (checkbox marcado)
    //   2. Validar los campos del formulario:
    //      - Si es excepcion: la evidencia es obligatoria
    //      - Si es normal: la evidencia es opcional
    //   3. Buscar el periodo en la base de datos para obtener el ciclo
    //   4. Determinar la evaluacion a usar:
    //      - Normal: usa la evaluacion del periodo activo
    //      - Excepcion: usa la evaluacion seleccionada en el dropdown
    //   5. Verificar que no exista una solicitud duplicada activa:
    //      - Se permite tener UNA solicitud normal Y UNA de excepcion
    //        al mismo tiempo para la misma materia
    //      - Solo se bloquea si ya hay una con el mismo tipo (normal/excepcion)
    //        que no haya sido rechazada
    //   6. Insertar la solicitud en la tabla solicitudes_correccion
    //   7. Si se subio evidencia, guardarla en Google Cloud Storage
    //      y registrarla en la tabla evidencias
    //   8. Redirigir al dashboard con mensaje de exito
    // =====================================================
    public function guardarSolicitud(Request $request)
    {
        // Detectar si el checkbox de excepcion esta marcado
        $esExcepcion = $request->has('es_excepcion') && $request->es_excepcion == 1;

        // Armar las reglas de validacion segun el tipo de solicitud
        $rules = [
            'materia_id'  => 'required|integer',
            'docente_id'  => 'required|integer',
            'seccion'     => 'required|string',
            'nota_actual' => 'required|numeric|min:0|max:10',
            'periodo_id'  => 'required|integer',
            'motivo'      => 'required|string|min:10',
            // La evidencia es obligatoria para excepciones, opcional para normales
            // Se usa evidencias.* para validar cada archivo individualmente
            'evidencias'   => $esExcepcion ? 'required|array' : 'nullable|array',
            'evidencias.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // Si es excepcion, tambien validar que se haya seleccionado una evaluacion
        if ($esExcepcion) {
            $rules['evaluacion_excepcion'] = 'required|string';
        }

        // Validar con mensajes personalizados
        $request->validate($rules, [
            'motivo.required'     => 'Debe ingresar el motivo del reclamo.',
            'motivo.min'          => 'El motivo debe tener al menos 10 caracteres.',
            'nota_actual.required' => 'Debe ingresar la nota que propone.',
            'nota_actual.numeric' => 'La nota debe ser un número válido.',
            'nota_actual.min'     => 'La nota no puede ser menor a 0.',
            'nota_actual.max'     => 'La nota no puede ser mayor a 10.',
            'evidencias.required' => 'La evidencia es obligatoria para solicitudes de excepción.',
            'evidencias.*.mimes'  => 'Solo se permiten archivos JPG, PNG o PDF.',
            'evidencias.*.max'    => 'Cada archivo no puede superar los 5MB.',
        ]);

        // Buscar el periodo en la base de datos para obtener su ciclo_id
        $periodo = DB::table('periodos_correccion')
            ->where('id', $request->periodo_id)
            ->first();

        if (!$periodo) {
            return redirect('/estudiante/nueva-solicitud')
                ->with('error', 'El periodo seleccionado no es válido.');
        }

        // Determinar cual evaluacion usar:
        // Si es excepcion, usar la del dropdown (evaluacion anterior)
        // Si es normal, usar la del periodo activo
        $evaluacion = $esExcepcion ? $request->evaluacion_excepcion : $periodo->evaluacion;

        // Verificar que no exista ya una solicitud activa del mismo tipo.
        // Se filtra por: mismo estudiante, misma materia, misma evaluacion,
        // mismo ciclo, mismo tipo (normal o excepcion).
        // Solo se consideran "activas" las que NO fueron rechazadas.
        $yaExiste = DB::table('solicitudes_correccion')
            ->where('estudiante_id', Auth::id())
            ->where('materia_id', $request->materia_id)
            ->where('evaluacion', $evaluacion)
            ->where('ciclo_id', $periodo->ciclo_id)
            ->where('es_excepcion', $esExcepcion ? 1 : 0)
            ->whereNotIn('estado', ['rechazado_docente', 'rechazado_coordinador'])
            ->exists();

        if ($yaExiste) {
            $tipoTexto = $esExcepcion ? 'excepción' : 'corrección';
            return redirect('/estudiante/nueva-solicitud')
                ->with('error', 'Ya tienes una solicitud de ' . $tipoTexto . ' activa para esta materia y evaluación. Puedes enviar una nueva solo si la anterior fue rechazada.');
        }

        // Buscar el ciclo academico para guardar su nombre en la solicitud
        $cicloData = DB::table('ciclos_academicos')
            ->where('id', $periodo->ciclo_id)
            ->first();

        if (!$cicloData) {
            return redirect('/estudiante/nueva-solicitud')
                ->with('error', 'No se encontró el ciclo académico asociado.');
        }

        // Insertar la solicitud en la base de datos.
        // El estado inicial siempre es 'pendiente_docente' porque
        // el docente es el primero en revisar la solicitud.
        try {
            $solicitudId = DB::table('solicitudes_correccion')->insertGetId([
                'estudiante_id'   => Auth::id(),
                'materia_id'      => $request->materia_id,
                'docente_id'      => $request->docente_id,
                'seccion'         => $request->seccion,
                'nota_actual'     => $request->nota_actual,
                'motivo'          => $request->motivo,
                'evaluacion'      => $evaluacion,
                'ciclo_id'        => $periodo->ciclo_id,
                'ciclo'           => $cicloData->nombre,
                'estado'          => 'pendiente_docente',
                'es_excepcion'    => $esExcepcion ? 1 : 0,
                'fecha_solicitud' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al insertar solicitud: ' . $e->getMessage());
            return redirect('/estudiante/nueva-solicitud')
                ->with('error', 'Error al guardar la solicitud: ' . $e->getMessage());
        }

        // Si el estudiante subio archivos de evidencia, guardar cada uno en
        // Google Cloud Storage (disco 'gcs') y registrarlo en la tabla evidencias
        if ($request->hasFile('evidencias')) {
            foreach ($request->file('evidencias') as $index => $archivo) {
                if ($archivo->isValid()) {
                    $extension = $archivo->getClientOriginalExtension();
                    $nombreArchivo = 'evidencia_estudiante_' . $solicitudId . '_' . time() . '_' . $index . '.' . $extension;
                    $rutaArchivo = $archivo->storeAs('evidencias', $nombreArchivo, 'gcs');

                    DB::table('evidencias')->insert([
                        'solicitud_id' => $solicitudId,
                        'usuario_id'   => Auth::id(),
                        'archivo'      => $rutaArchivo,
                        'descripcion'  => 'Evidencia adjuntada por estudiante',
                        'fecha'        => now(),
                    ]);
                }
            }
        }

        // Mensaje de exito personalizado segun el tipo de solicitud
        $mensaje = $esExcepcion
            ? '¡Tu solicitud de excepción ha sido enviada al docente con éxito!'
            : '¡Tu solicitud ha sido enviada al docente con éxito!';

        return redirect('/estudiante/dashboard')
            ->with('success', $mensaje);
    }

    // =====================================================
    // CANCELAR SOLICITUD
    //
    // Permite al estudiante cancelar una solicitud que envio
    // por error. Solo se puede cancelar si:
    //   1. La solicitud pertenece al estudiante logueado
    //   2. El estado es 'pendiente_docente' (el docente no ha actuado)
    //   3. Han pasado menos de 3 horas desde que se creo
    //
    // Al cancelar, se eliminan todos los registros relacionados:
    //   - Evidencias del estudiante (archivos en GCS + registros en BD)
    //   - La solicitud misma
    // =====================================================
    public function cancelarSolicitud($id)
    {
        $solicitud = DB::table('solicitudes_correccion')
            ->where('id', $id)
            ->where('estudiante_id', Auth::id())
            ->first();

        if (!$solicitud) {
            return redirect('/estudiante/dashboard')
                ->with('error', 'Solicitud no encontrada o no tienes permiso para cancelarla.');
        }

        // Solo se puede cancelar si aun esta en pendiente_docente
        if ($solicitud->estado !== 'pendiente_docente') {
            return redirect('/estudiante/solicitud/' . $id)
                ->with('error', 'No se puede cancelar esta solicitud porque el docente ya la revisó.');
        }

        // Verificar que no hayan pasado mas de 3 horas desde la creacion
        $fechaCreacion = \Carbon\Carbon::parse($solicitud->fecha_solicitud);
        $horasTranscurridas = $fechaCreacion->diffInMinutes(now());

        if ($horasTranscurridas > 180) {
            return redirect('/estudiante/solicitud/' . $id)
                ->with('error', 'No se puede cancelar esta solicitud porque ya pasaron más de 3 horas desde que fue enviada.');
        }

        // Eliminar evidencias asociadas (archivos en GCS + registros en BD)
        $evidencias = DB::table('evidencias')
            ->where('solicitud_id', $id)
            ->get();

        foreach ($evidencias as $evidencia) {
            \Illuminate\Support\Facades\Storage::disk('gcs')->delete($evidencia->archivo);
            DB::table('evidencias')->where('id', $evidencia->id)->delete();
        }

        // Eliminar la solicitud
        DB::table('solicitudes_correccion')->where('id', $id)->delete();

        return redirect('/estudiante/dashboard')
            ->with('success', 'Tu solicitud ha sido cancelada exitosamente.');
    }

    // =====================================================
    // VER DETALLE: Muestra toda la informacion de una solicitud
    //
    // Solo el estudiante que creo la solicitud puede verla.
    // Muestra:
    //   - Datos de la solicitud (materia, nota, motivo, etc.)
    //   - Historial de aprobaciones (todas las decisiones tomadas
    //     por docente, coordinador y admin)
    //   - La ultima accion de cada fase (para el timeline visual)
    //   - Historial de nota (solo si ya fue finalizada por admin)
    // =====================================================
    public function verDetalle($id)
    {
        // Traer la solicitud con JOIN a materias y docentes
        // Solo permite ver solicitudes del estudiante logueado (seguridad)
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('solicitudes_correccion.estudiante_id', Auth::id())
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
                'docentes.nombre as docente_nombre'
            )
            ->first();

        if (!$solicitud) {
            return redirect('/estudiante/dashboard')
                ->with('error', 'Solicitud no encontrada o no tienes permiso para verla.');
        }

        // Traer todo el historial de aprobaciones de esta solicitud,
        // ordenado por fecha para mostrar la linea de tiempo completa
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

        // Para el timeline, buscar la ultima accion de cada fase.
        // Filtramos por texto porque el campo 'accion' contiene frases como
        // "Aprobado por docente", "Rechazado por coordinador", etc.
        $accionDocente      = $aprobaciones->filter(fn($a) => stripos($a->accion, 'docente') !== false)->last();
        $accionCoordinador  = $aprobaciones->filter(fn($a) => stripos($a->accion, 'coordinador') !== false)->last();
        $accionAdmin        = $aprobaciones->filter(fn($a) => stripos($a->accion, 'admin') !== false)->last();

        // El historial de nota solo existe si la solicitud ya fue finalizada por el admin
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
    // EXPORTAR PDF: Genera la constancia de correccion en PDF
    //
    // Solo funciona para solicitudes finalizadas del estudiante.
    // Recopila toda la informacion necesaria:
    //   - Datos de la solicitud, materia, carrera, facultad
    //   - Decisiones de docente, coordinador y admin
    //   - Historial de notas (nota anterior y nota nueva)
    // Genera el PDF con la vista 'pdf.constancia_correccion'
    // y lo descarga con un nombre que incluye el ID de la solicitud.
    // =====================================================
    public function exportarPdf($id)
    {
        // Traer solicitud con todos los JOINs necesarios para la constancia
        // Solo permite exportar solicitudes finalizadas del estudiante logueado
        $solicitud = DB::table('solicitudes_correccion')
            ->join('materias', 'solicitudes_correccion.materia_id', '=', 'materias.id')
            ->join('carreras', 'materias.carrera_id', '=', 'carreras.id')
            ->join('facultades', 'carreras.facultad_id', '=', 'facultades.id')
            ->join('usuarios as estudiantes', 'solicitudes_correccion.estudiante_id', '=', 'estudiantes.id')
            ->join('usuarios as docentes', 'solicitudes_correccion.docente_id', '=', 'docentes.id')
            ->where('solicitudes_correccion.id', $id)
            ->where('solicitudes_correccion.estudiante_id', Auth::id())
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
            return redirect('/estudiante/dashboard')
                ->with('error', 'Solo puedes descargar la constancia de tus solicitudes finalizadas.');
        }

        // Traer el registro de nota anterior y nota nueva
        $historialNota = DB::table('historial_notas')
            ->where('solicitud_id', $id)->orderBy('fecha', 'desc')->first();

        if (!$historialNota) {
            return redirect('/estudiante/dashboard')
                ->with('error', 'No se encontró el historial de notas para generar la constancia.');
        }

        // Traer la decision de cada nivel para incluirla en la constancia
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

        // Generar el PDF usando la vista de constancia y descargarlo
        $pdf = Pdf::loadView('pdf.constancia_correccion', compact(
            'solicitud', 'historialNota', 'decisionDocente',
            'decisionCoordinador', 'decisionAdmin', 'fechaEmision'
        ))->setPaper('letter');

        return $pdf->download('Constancia_SCN-' . str_pad($solicitud->id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }
}
