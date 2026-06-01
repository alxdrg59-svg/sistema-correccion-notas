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
                    ];
                    $etiquetas = [
                        'pendiente_docente'     => 'En revisión (Docente)',
                        'rechazado_docente'     => 'Rechazada por Docente',
                        'pendiente_coordinador' => 'En revisión (Coordinador)',
                        'rechazado_coordinador' => 'Rechazada por Coordinador',
                        'pendiente_admin'       => 'En revisión (Admin. Académico)',
                        'finalizado'            => 'Aprobada y Finalizada',
                    ];
                    $iconos = [
                        'pendiente_docente'     => 'fa-hourglass-half',
                        'rechazado_docente'     => 'fa-times-circle',
                        'pendiente_coordinador' => 'fa-hourglass-half',
                        'rechazado_coordinador' => 'fa-times-circle',
                        'pendiente_admin'       => 'fa-hourglass-half',
                        'finalizado'            => 'fa-check-circle',
                    ];
                    $estadoKey = $solicitud->estado;
                    $estilo    = $clases[$estadoKey]    ?? 'bg-gray-100 text-gray-600 border-gray-300';
                    $etiqueta  = $etiquetas[$estadoKey] ?? $estadoKey;
                    $icono     = $iconos[$estadoKey]    ?? 'fa-circle';
                @endphp
                <div class="flex items-center gap-2">
                    @if($solicitud->es_excepcion)
                        <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-300 px-3 py-1.5 rounded-full text-xs font-bold uppercase">
                            <i class="fas fa-exclamation-circle"></i> Excepción
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
        @if($solicitud->estado === 'pendiente_docente')
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
                        $docenteAprobó  = $docenteActuo && stripos($accionDocente->accion, 'rechazado') === false;
                        $docenteRechazó = $docenteActuo && stripos($accionDocente->accion, 'rechazado') !== false;
                    @endphp
                    <div class="relative flex items-start gap-4 mb-8 z-10">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center shadow-md
                            {{ $docenteRechazó ? 'bg-red-500' : ($docenteAprobó ? 'bg-green-500' : 'bg-orange-400') }}">
                            <i class="fas {{ $docenteRechazó ? 'fa-times' : ($docenteAprobó ? 'fa-check' : 'fa-hourglass-half') }} text-white text-sm"></i>
                        </div>
                        <div class="flex-1 pt-1">
                            <p class="font-bold text-gray-800 text-sm uppercase tracking-wide">Revisión del Docente</p>
                            @if($docenteActuo)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($accionDocente->fecha)->format('d/m/Y H:i') }}
                                    — {{ $accionDocente->actor_nombre }}
                                </p>
                                <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full
                                    {{ $docenteRechazó ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $accionDocente->accion }}
                                </span>
                                @if($accionDocente->comentario)
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 border rounded p-2 leading-relaxed italic">
                                        "{{ $accionDocente->comentario }}"
                                    </p>
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
                        $adminBloqueado = !$coordinadorAprobó;
                    @endphp
                    <div class="relative flex items-start gap-4 z-10">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center shadow-md
                            {{ $adminBloqueado ? 'bg-gray-300' : ($adminActuo ? 'bg-green-500' : 'bg-blue-400') }}">
                            <i class="fas {{ $adminBloqueado ? 'fa-lock' : ($adminActuo ? 'fa-flag-checkered' : 'fa-hourglass-half') }} text-white text-sm"></i>
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
                                <span class="inline-block mt-1 text-xs font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                    {{ $accionAdmin->accion }}
                                </span>
                                @if($accionAdmin->comentario)
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50 border rounded p-2 leading-relaxed italic">
                                        "{{ $accionAdmin->comentario }}"
                                    </p>
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
