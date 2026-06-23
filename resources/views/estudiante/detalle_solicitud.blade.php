@extends('layouts.app')

@section('title', 'Detalle de Solicitud')
@section('subtitle', 'Portal Académico')
@section('rol', 'Estudiante')
@section('body-class', 'pb-12')

@section('content')
    <div class="container mx-auto max-w-4xl mt-8 px-4">

        {{-- Botón volver --}}
        <a href="/estudiante/dashboard"
            style="color: #5D0A28;"
            class="inline-flex items-center font-bold text-sm hover:underline mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Mis Solicitudes
        </a>

        {{-- DATOS PRINCIPALES DE LA SOLICITUD --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-2" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-file-alt"></i> Datos de la Solicitud
                </h2>
                @php
                    $clases = [
                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-300',
                        'rechazado_docente'     => 'bg-red-100    text-red-700    border-red-300',
                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-300',
                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-300',
                        'finalizado'            => 'bg-green-100  text-green-700  border-green-300',
                        'requiere_evidencia'    => 'bg-purple-100 text-purple-700 border-purple-300',
                    ];
                    $etiquetas = [
                        'pendiente_docente'     => 'En revisión (Docente)',
                        'rechazado_docente'     => 'Rechazada por Docente',
                        'pendiente_coordinador' => 'En revisión (Coordinador)',
                        'rechazado_coordinador' => 'Rechazada por Coordinador',
                        'pendiente_admin'       => 'En revisión (Admin. Académico)',
                        'finalizado'            => 'Aprobada y Finalizada',
                        'requiere_evidencia'    => 'El docente solicita más evidencia',
                    ];
                    $iconos = [
                        'pendiente_docente'     => 'fa-hourglass-half',
                        'rechazado_docente'     => 'fa-times-circle',
                        'pendiente_coordinador' => 'fa-hourglass-half',
                        'rechazado_coordinador' => 'fa-times-circle',
                        'pendiente_admin'       => 'fa-hourglass-half',
                        'finalizado'            => 'fa-check-circle',
                        'requiere_evidencia'    => 'fa-file-upload',
                    ];
                    $estadoKey = $solicitud->estado;
                    $estilo    = $clases[$estadoKey]    ?? 'bg-gray-100 text-gray-600 border-gray-300';
                    $etiqueta  = $etiquetas[$estadoKey] ?? $estadoKey;
                    $icono     = $iconos[$estadoKey]    ?? 'fa-circle';
                @endphp
                <div class="flex items-center gap-2">
                    @if($solicitud->es_excepcion == 1)
                        <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-300 px-3 py-1.5 rounded-full text-xs font-bold uppercase">
                            <i class="fas fa-exclamation-circle"></i> Excepción
                        </span>
                    @elseif($solicitud->es_excepcion == 2)
                        <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 border border-indigo-300 px-3 py-1.5 rounded-full text-xs font-bold uppercase">
                            <i class="fas fa-clock"></i> Diferido
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $estilo }}">
                        <i class="fas {{ $icono }} text-[10px]"></i> {{ $etiqueta }}
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Materia</p>
                    <p class="text-gray-800 font-bold text-base">{{ $solicitud->materia_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Docente</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->docente_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Evaluación</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->evaluacion }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ciclo</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->ciclo }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sección</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->seccion }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nota Reclamada</p>
                    <p class="text-2xl font-extrabold" style="color: #5D0A28;">{{ $solicitud->nota_actual }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Justificación del Reclamo</p>
                    <p class="text-gray-700 bg-gray-50 border rounded-lg p-3 text-sm leading-relaxed">
                        {{ $solicitud->motivo }}
                    </p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Envío</p>
                    <p class="text-gray-600 text-sm font-medium">
                        {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- BOTON DE CANCELAR SOLICITUD --}}
        @if($solicitud->estado === 'pendiente_docente' && $aprobaciones->isEmpty())
            @php
                $fechaCreacion = \Carbon\Carbon::parse($solicitud->fecha_solicitud);
                $fechaLimite = $fechaCreacion->copy()->addHours(3);
                $puedeCancel = now()->lt($fechaLimite);
                $minutosRestantes = $puedeCancel ? now()->diffInMinutes($fechaLimite) : 0;
                $horasR = floor($minutosRestantes / 60);
                $minsR = $minutosRestantes % 60;
            @endphp

            @if($puedeCancel)
            <div class="bg-orange-50 border-2 border-orange-300 rounded-xl p-5 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-bold text-orange-800 flex items-center gap-2">
                            <i class="fas fa-clock text-orange-500"></i>
                            Puedes cancelar esta solicitud
                        </p>
                        <p class="text-xs text-orange-600 mt-1">
                            Tiempo restante:
                            <span class="font-bold">
                                {{ $horasR > 0 ? $horasR . 'h ' : '' }}{{ $minsR }}min
                            </span>
                            — Después de este plazo, la solicitud no podrá ser cancelada.
                        </p>
                    </div>
                    <form action="/estudiante/solicitud/{{ $solicitud->id }}/cancelar" method="POST"
                        onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta solicitud? Esta acción no se puede deshacer.')">
                        @csrf
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-2 shadow whitespace-nowrap">
                            <i class="fas fa-times-circle"></i> Cancelar Solicitud
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
                <p class="text-xs text-gray-500 flex items-center gap-2">
                    <i class="fas fa-lock text-gray-400"></i>
                    El plazo de 3 horas para cancelar esta solicitud ha expirado.
                </p>
            </div>
            @endif
        @endif

        {{-- FORMULARIO DE EVIDENCIA SOLICITADA POR DOCENTE --}}
        @if($solicitud->estado === 'requiere_evidencia')
        <div class="bg-purple-50 border-2 border-purple-300 rounded-xl shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 bg-purple-600">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-file-upload"></i> El Docente Solicita Más Evidencia
                </h2>
            </div>
            <div class="p-6">
                @if($solicitudEvidencia)
                <div class="bg-white border border-purple-200 rounded-lg p-4 mb-5">
                    <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-1">
                        <i class="fas fa-comment-dots mr-1"></i> Mensaje del Docente
                        ({{ $solicitudEvidencia->actor_nombre }} — {{ \Carbon\Carbon::parse($solicitudEvidencia->fecha)->format('d/m/Y H:i') }})
                    </p>
                    <p class="text-gray-700 text-sm leading-relaxed italic">
                        "{{ $solicitudEvidencia->comentario }}"
                    </p>
                </div>
                @endif

                @if($evidenciasDocente->count())
                <div class="bg-white border border-purple-200 rounded-lg p-4 mb-5">
                    <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-paperclip mr-1"></i> Archivos adjuntos por el Docente ({{ $evidenciasDocente->count() }} archivo{{ $evidenciasDocente->count() > 1 ? 's' : '' }})
                    </p>
                    @foreach($evidenciasDocente as $evDoc)
                    <div class="flex items-center gap-3 {{ !$loop->first ? 'border-t border-purple-100 pt-2 mt-2' : '' }}">
                        <a href="{{ route('evidencia.ver', $evDoc->id) }}" target="_blank"
                            style="color: #5D0A28;"
                            class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="{{ route('evidencia.descargar', $evDoc->id) }}"
                            style="color: #5D0A28;"
                            class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                        <span class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($evDoc->fecha)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif

                <form action="/estudiante/solicitud/{{ $solicitud->id }}/agregar-evidencia"
                    method="POST"
                    enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                            Adjuntar Evidencia
                            <span class="text-red-500 ml-1">(Obligatorio)</span>
                            <span class="text-gray-400 font-normal normal-case ml-1">— JPG, PNG, PDF — máx. 5MB por archivo</span>
                        </label>
                        <div class="border-2 border-dashed border-purple-300 rounded-lg p-6 text-center transition cursor-pointer hover:border-purple-500"
                            id="zona_evidencia_est">
                            <i class="fas fa-cloud-upload-alt text-3xl text-purple-400 mb-2"></i>
                            <p class="text-sm text-gray-500">Haz clic o arrastra archivos aquí</p>
                            <p class="text-xs text-gray-400 mt-1 italic">Puede agregar varios archivos</p>
                        </div>
                        <input type="file" id="input_selector_est" accept=".jpg,.jpeg,.png,.pdf" multiple class="hidden">
                        <div id="contenedor_inputs_est"></div>
                        <div id="lista_archivos_est" class="hidden mt-3 space-y-1.5"></div>
                    </div>

                    <button type="submit" id="btn_enviar_evidencia"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest text-sm">
                        <i class="fas fa-paper-plane mr-2"></i> Enviar Evidencia al Docente
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- RESULTADO FINAL DE LA CORRECCIÓN --}}
        @if($solicitud->estado === 'finalizado' && $historialNota)
        <div class="bg-green-50 border-2 border-green-400 rounded-xl shadow-md p-6 mb-6">
            <h3 class="text-green-800 font-extrabold text-lg uppercase tracking-wider flex items-center gap-2 mb-4">
                <i class="fas fa-trophy text-green-600"></i> Resultado Final de la Corrección
            </h3>
            <div class="flex items-center justify-center gap-8 flex-wrap">
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nota Propuesta</p>
                    <p class="text-4xl font-extrabold text-red-500">{{ $historialNota->nota_anterior }}</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-arrow-right text-3xl text-gray-400"></i>
                </div>
                <div class="text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nota Nueva</p>
                    <p class="text-4xl font-extrabold text-green-600">{{ $historialNota->nota_nueva }}</p>
                </div>
            </div>
            <p class="text-center text-xs text-gray-500 mt-4 italic">
                Corrección registrada el {{ \Carbon\Carbon::parse($historialNota->fecha)->format('d/m/Y H:i') }}
            </p>
            <div class="text-center mt-4">
                <a href="/estudiante/solicitud/{{ $solicitud->id }}/pdf"
                    style="background-color: #5D0A28;"
                    onmouseover="this.style.backgroundColor='#4A0820'"
                    onmouseout="this.style.backgroundColor='#5D0A28'"
                    class="text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-2 shadow">
                    <i class="fas fa-file-pdf"></i> Descargar Constancia PDF
                </a>
            </div>
        </div>
        @endif

        {{-- SEGUIMIENTO DE LA SOLICITUD --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-stream"></i> Seguimiento de la Solicitud
                </h2>
            </div>

            <div class="p-6">
                <div class="relative">
                    {{-- Línea conectora vertical --}}
                    <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gray-200 z-0"></div>

                    {{-- PASO 1: Enviada (siempre completo) --}}
                    <div class="relative flex items-start gap-4 mb-8 z-10">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-500 flex items-center justify-center shadow-md">
                            <i class="fas fa-paper-plane text-white text-sm"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-bold text-gray-800 text-sm uppercase tracking-wide">Solicitud Enviada</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                            </p>
                            <p class="text-xs text-green-600 font-semibold mt-1">
                                Tu solicitud fue enviada correctamente al docente.
                            </p>
                        </div>
                    </div>

                    {{-- Docente --}}
                    @php
                        $docenteActuo   = !is_null($accionDocente);
                        $docentePidioEvidencia = $docenteActuo && stripos($accionDocente->accion, 'evidencia') !== false;
                        $docenteAprobó  = $docenteActuo && stripos($accionDocente->accion, 'aprobado') !== false;
                        $docenteRechazó = $docenteActuo && stripos($accionDocente->accion, 'rechazado') !== false;
                    @endphp
                    <div class="relative flex items-start gap-4 mb-8 z-10">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center shadow-md
                            {{ $docenteRechazó ? 'bg-red-500' : ($docenteAprobó ? 'bg-green-500' : ($docentePidioEvidencia ? 'bg-purple-500' : 'bg-orange-400')) }}">
                            <i class="fas {{ $docenteRechazó ? 'fa-times' : ($docenteAprobó ? 'fa-check' : ($docentePidioEvidencia ? 'fa-file-upload' : 'fa-hourglass-half')) }} text-white text-sm"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-bold text-gray-800 text-sm uppercase tracking-wide">Revisión del Docente</p>
                            @if($docenteActuo)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($accionDocente->fecha)->format('d/m/Y H:i') }}
                                    — {{ $accionDocente->actor_nombre }}
                                </p>
                                <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $docenteRechazó ? 'bg-red-100 text-red-700' : ($docentePidioEvidencia ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700') }}">
                                    {{ $accionDocente->accion }}
                                </span>
                                @if($accionDocente->comentario)
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 border rounded p-2 leading-relaxed italic">
                                        "{{ $accionDocente->comentario }}"
                                    </p>
                                @endif
                                @if($docenteRechazó)
                                    <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-xs text-red-700 font-semibold flex items-center gap-1.5">
                                            <i class="fas fa-info-circle"></i> Acércate a tu docente para mayor detalle.
                                        </p>
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-orange-500 font-semibold mt-1">
                                    <i class="fas fa-clock mr-1"></i> Pendiente de revisión por el docente...
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Coordinador de Facultad --}}
                    @php
                        $coordinadorActuo   = !is_null($accionCoordinador);
                        $coordinadorAprobó  = $coordinadorActuo && stripos($accionCoordinador->accion, 'rechazado') === false;
                        $coordinadorRechazó = $coordinadorActuo && stripos($accionCoordinador->accion, 'rechazado') !== false;
                        $coordinadorBloqueado = !$docenteAprobó;
                    @endphp
                    <div class="relative flex items-start gap-4 mb-8 z-10">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center shadow-md
                            {{ $coordinadorBloqueado ? 'bg-gray-300' : ($coordinadorRechazó ? 'bg-red-500' : ($coordinadorAprobó ? 'bg-green-500' : 'bg-yellow-400')) }}">
                            <i class="fas {{ $coordinadorBloqueado ? 'fa-lock' : ($coordinadorRechazó ? 'fa-times' : ($coordinadorAprobó ? 'fa-check' : 'fa-hourglass-half')) }} text-white text-sm"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-bold text-sm uppercase tracking-wide {{ $coordinadorBloqueado ? 'text-gray-400' : 'text-gray-800' }}">
                                Revisión del Coordinador de Facultad
                            </p>
                            @if($coordinadorBloqueado)
                                <p class="text-xs text-gray-400 mt-1 italic">En espera de la revisión del docente.</p>
                            @elseif($coordinadorActuo)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($accionCoordinador->fecha)->format('d/m/Y H:i') }}
                                    — {{ $accionCoordinador->actor_nombre }}
                                </p>
                                <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $coordinadorRechazó ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $accionCoordinador->accion }}
                                </span>
                                @if($accionCoordinador->comentario)
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 border rounded p-2 leading-relaxed italic">
                                        "{{ $accionCoordinador->comentario }}"
                                    </p>
                                @endif
                                @if($coordinadorRechazó)
                                    <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-xs text-red-700 font-semibold flex items-center gap-1.5">
                                            <i class="fas fa-info-circle"></i> Acércate a tu docente para mayor detalle.
                                        </p>
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-yellow-600 font-semibold mt-1">
                                    <i class="fas fa-clock mr-1"></i> Pendiente de revisión por el coordinador...
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Cierre Administrativo --}}
                    @php
                        $adminActuo     = !is_null($accionAdmin);
                        $adminRechazó   = $adminActuo && stripos($accionAdmin->accion, 'rechazado') !== false;
                        $adminAprobó    = $adminActuo && !$adminRechazó;
                        $adminBloqueado = !$coordinadorAprobó;
                    @endphp
                    <div class="relative flex items-start gap-4 z-10">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center shadow-md
                            {{ $adminBloqueado ? 'bg-gray-300' : ($adminRechazó ? 'bg-red-500' : ($adminAprobó ? 'bg-green-500' : 'bg-blue-400')) }}">
                            <i class="fas {{ $adminBloqueado ? 'fa-lock' : ($adminRechazó ? 'fa-times' : ($adminAprobó ? 'fa-flag-checkered' : 'fa-hourglass-half')) }} text-white text-sm"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-bold text-sm uppercase tracking-wide {{ $adminBloqueado ? 'text-gray-400' : 'text-gray-800' }}">
                                Cierre Administrativo
                            </p>
                            @if($adminBloqueado)
                                <p class="text-xs text-gray-400 mt-1 italic">En espera de aprobación del coordinador.</p>
                            @elseif($adminActuo)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($accionAdmin->fecha)->format('d/m/Y H:i') }}
                                    — {{ $accionAdmin->actor_nombre }}
                                </p>
                                <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $adminRechazó ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $accionAdmin->accion }}
                                </span>
                                @if($accionAdmin->comentario)
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 border rounded p-2 leading-relaxed italic">
                                        "{{ $accionAdmin->comentario }}"
                                    </p>
                                @endif
                                @if($adminRechazó)
                                    <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                        <p class="text-xs text-red-700 font-semibold flex items-center gap-1.5">
                                            <i class="fas fa-info-circle"></i> Acércate a tu docente para mayor detalle.
                                        </p>
                                    </div>
                                @endif
                            @else
                                <p class="text-xs text-blue-600 font-semibold mt-1">
                                    <i class="fas fa-clock mr-1"></i> Pendiente de cierre administrativo...
                                </p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@if($solicitud->estado === 'requiere_evidencia')
@section('scripts')
<script>
    var archivosEst = [];
    var zonaEst = document.getElementById('zona_evidencia_est');
    var inputSelectorEst = document.getElementById('input_selector_est');
    var listaDivEst = document.getElementById('lista_archivos_est');
    var contenedorInputsEst = document.getElementById('contenedor_inputs_est');
    var extPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];

    if (zonaEst) {
        zonaEst.addEventListener('click', function () { inputSelectorEst.click(); });
        inputSelectorEst.addEventListener('change', function () {
            agregarArchivosEst(this.files);
            this.value = '';
        });
        zonaEst.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.style.borderColor = '#7c3aed';
            this.style.backgroundColor = '#faf5ff';
        });
        zonaEst.addEventListener('dragleave', function (e) {
            e.preventDefault();
            this.style.backgroundColor = '';
            this.style.borderColor = '#c4b5fd';
        });
        zonaEst.addEventListener('drop', function (e) {
            e.preventDefault();
            this.style.backgroundColor = '';
            this.style.borderColor = '#c4b5fd';
            if (e.dataTransfer.files.length > 0) agregarArchivosEst(e.dataTransfer.files);
        });
    }

    function agregarArchivosEst(fileList) {
        for (var i = 0; i < fileList.length; i++) {
            var archivo = fileList[i];
            var ext = archivo.name.split('.').pop().toLowerCase();
            if (extPermitidas.indexOf(ext) === -1) { alert('"' + archivo.name + '" no es un formato permitido.'); continue; }
            if (archivo.size > 5 * 1024 * 1024) { alert('"' + archivo.name + '" supera los 5MB.'); continue; }
            var dup = false;
            for (var j = 0; j < archivosEst.length; j++) {
                if (archivosEst[j].name === archivo.name && archivosEst[j].size === archivo.size) { dup = true; break; }
            }
            if (!dup) archivosEst.push(archivo);
        }
        renderListaEst();
        sincronizarInputsEst();
    }

    function eliminarArchivoEst(idx) {
        archivosEst.splice(idx, 1);
        renderListaEst();
        sincronizarInputsEst();
    }

    function eliminarTodosEst() {
        archivosEst = [];
        renderListaEst();
        sincronizarInputsEst();
    }

    function renderListaEst() {
        listaDivEst.innerHTML = '';
        if (archivosEst.length === 0) { listaDivEst.classList.add('hidden'); return; }
        listaDivEst.classList.remove('hidden');
        var enc = document.createElement('div');
        enc.className = 'flex items-center justify-between';
        enc.innerHTML = '<p class="text-xs font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1.5">' +
            '<i class="fas fa-paperclip"></i> ' + archivosEst.length + ' archivo' + (archivosEst.length > 1 ? 's' : '') +
            ' adjunto' + (archivosEst.length > 1 ? 's' : '') + '</p>' +
            '<button type="button" onclick="eliminarTodosEst()" class="text-xs text-red-500 hover:text-red-700 font-bold"><i class="fas fa-trash-alt mr-1"></i>Quitar todos</button>';
        listaDivEst.appendChild(enc);
        for (var i = 0; i < archivosEst.length; i++) {
            var a = archivosEst[i];
            var t = (a.size / 1024 / 1024).toFixed(2);
            var ex = a.name.split('.').pop().toUpperCase();
            var ic = ex === 'PDF' ? 'fa-file-pdf text-red-500' : 'fa-file-image text-blue-500';
            var f = document.createElement('div');
            f.className = 'flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm';
            f.innerHTML = '<i class="fas ' + ic + ' text-lg"></i>' +
                '<span class="font-medium text-gray-700 truncate flex-1">' + a.name + '</span>' +
                '<span class="text-xs text-gray-400 font-mono whitespace-nowrap">' + t + ' MB</span>' +
                '<span class="text-xs font-bold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600">' + ex + '</span>' +
                '<button type="button" onclick="eliminarArchivoEst(' + i + ')" class="text-red-400 hover:text-red-600 ml-1" title="Quitar"><i class="fas fa-times-circle"></i></button>';
            listaDivEst.appendChild(f);
        }
    }

    function sincronizarInputsEst() {
        contenedorInputsEst.innerHTML = '';
        for (var i = 0; i < archivosEst.length; i++) {
            var dt = new DataTransfer();
            dt.items.add(archivosEst[i]);
            var inp = document.createElement('input');
            inp.type = 'file'; inp.name = 'evidencias[]'; inp.files = dt.files; inp.style.display = 'none';
            contenedorInputsEst.appendChild(inp);
        }
    }
</script>
@endsection
@endif
