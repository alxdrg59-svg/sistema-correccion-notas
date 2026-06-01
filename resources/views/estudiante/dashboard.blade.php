@extends('layouts.app')

@section('title', 'Panel Estudiante')
@section('subtitle', 'Portal Académico')
@section('rol', 'Estudiante')

@section('content')
    <div class="container mx-auto mt-2 p-4">

        {{-- Encabezado + botón de nueva solicitud --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800">Mis Solicitudes</h2>
                <p class="text-gray-500 italic text-sm mt-1">Seguimiento de tus correcciones de notas enviadas.</p>
            </div>
            {{-- Redirige al formulario de nueva solicitud --}}
            <a href="/estudiante/nueva-solicitud"
                style="background-color: #5D0A28;"
                onmouseover="this.style.backgroundColor='#4A0820'"
                onmouseout="this.style.backgroundColor='#5D0A28'"
                class="text-white px-6 py-3 rounded-lg font-bold shadow-lg transition transform hover:scale-105 flex items-center text-sm uppercase tracking-wide whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i> Nueva Solicitud
            </a>
        </div>

        {{-- Filtros --}}
        <div class="mb-4 flex flex-wrap gap-2" id="filtros">
            <button onclick="filtrar('todas')" class="filtro-btn activo px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="todas">Todas</button>
            <button onclick="filtrar('pendiente')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="pendiente">Pendientes</button>
            <button onclick="filtrar('rechazada')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="rechazada">Rechazadas</button>
            <button onclick="filtrar('finalizado')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="finalizado">Finalizadas</button>
            <button onclick="filtrar('excepcion')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="excepcion">Excepciones</button>
        </div>

        {{-- TABLA DE SOLICITUDES --}}
        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Materia / Evaluación</th>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Fecha de Solicitud</th>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $solicitud)
                        @php
                            $categoria = 'pendiente';
                            if (str_contains($solicitud->estado, 'rechazado')) $categoria = 'rechazada';
                            elseif ($solicitud->estado === 'finalizado') $categoria = 'finalizado';
                        @endphp
                        <tr class="border-b hover:bg-gray-50 transition fila-solicitud" data-estado="{{ $categoria }}" data-excepcion="{{ $solicitud->es_excepcion ? '1' : '0' }}">

                            {{-- Nombre de materia via relación Eloquent + evaluación y ciclo --}}
                            <td class="p-4">
                                <p class="font-bold text-gray-800">
                                    {{ $solicitud->materiaRelacion->nombre ?? 'Sin nombre de materia' }}
                                </p>
                                <p class="text-xs text-gray-500 uppercase font-semibold tracking-tight mt-0.5">
                                    {{ $solicitud->evaluacion ?? '—' }} &mdash; {{ $solicitud->ciclo ?? '—' }}
                                </p>
                                @if($solicitud->es_excepcion)
                                    <span class="inline-flex items-center gap-1 mt-1 bg-amber-100 text-amber-700 border border-amber-300 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                        <i class="fas fa-exclamation-circle"></i> Excepción
                                    </span>
                                @endif
                            </td>

                            {{-- BADGE DE ESTADO --}}
                            <td class="p-4 text-center">
                                @php
                                    $clases = [
                                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'rechazado_docente'     => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-200',
                                        'finalizado'            => 'bg-green-100  text-green-700  border-green-200',
                                    ];
                                    $iconos = [
                                        'pendiente_docente'     => 'fa-hourglass-half',
                                        'rechazado_docente'     => 'fa-times-circle',
                                        'pendiente_coordinador' => 'fa-hourglass-half',
                                        'rechazado_coordinador' => 'fa-times-circle',
                                        'pendiente_admin'       => 'fa-hourglass-half',
                                        'finalizado'            => 'fa-check-circle',
                                    ];
                                    $etiquetas = [
                                        'pendiente_docente'     => 'En revisión (Docente)',
                                        'rechazado_docente'     => 'Rechazada',
                                        'pendiente_coordinador' => 'En revisión (Coordinador)',
                                        'rechazado_coordinador' => 'Rechazada',
                                        'pendiente_admin'       => 'En revisión (Admin. Académico)',
                                        'finalizado'            => 'Aprobada y Finalizada',
                                    ];
                                    $estadoKey = $solicitud->estado;
                                    $estilo    = $clases[$estadoKey]    ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                    $icono     = $iconos[$estadoKey]    ?? 'fa-circle';
                                    $etiqueta  = $etiquetas[$estadoKey] ?? $estadoKey;
                                @endphp

                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase border {{ $estilo }}">
                                    <i class="fas {{ $icono }} text-[10px]"></i>
                                    {{ $etiqueta }}
                                </span>
                            </td>

                            {{-- FECHA DE SOLICITUD --}}
                            <td class="p-4 text-sm text-gray-500 font-medium">
                                @if($solicitud->fecha_solicitud)
                                    {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                                @else
                                    <span class="italic text-gray-400">Reciente</span>
                                @endif
                            </td>

                            {{-- Enlace a la vista de detalle con el ID de la solicitud --}}
                            <td class="p-4 text-right">
                                <div class="flex flex-col items-end gap-1.5">
                                    <a href="/estudiante/solicitud/{{ $solicitud->id }}"
                                        style="color: #5D0A28;"
                                        onmouseover="this.style.color='#4A0820'"
                                        onmouseout="this.style.color='#5D0A28'"
                                        class="hover:underline font-bold text-xs uppercase tracking-widest transition">
                                        <i class="fas fa-eye mr-1"></i> Ver Detalles
                                    </a>
                                    @if($solicitud->estado === 'finalizado')
                                        <a href="/estudiante/solicitud/{{ $solicitud->id }}/pdf"
                                            class="text-green-700 hover:text-green-900 font-bold text-xs uppercase tracking-widest transition hover:underline">
                                            <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                                        </a>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">Aún no tienes solicitudes</p>
                                    <p class="text-sm mt-1 italic">Cuando envíes una corrección de nota, aparecerá aquí.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Referencias visuales de los posibles estados --}}
        <div class="mt-6 flex flex-wrap gap-3 text-xs items-center">
            <span class="font-bold text-gray-500 uppercase tracking-wider">Referencias de Estado:</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-orange-100 text-orange-700 border-orange-200 font-semibold">
                <i class="fas fa-hourglass-half text-[10px]"></i> En revisión (Docente)
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-yellow-100 text-yellow-700 border-yellow-200 font-semibold">
                <i class="fas fa-hourglass-half text-[10px]"></i> En revisión (Coordinador)
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-blue-100 text-blue-700 border-blue-200 font-semibold">
                <i class="fas fa-hourglass-half text-[10px]"></i> En revisión (Admin. Académico)
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-green-100 text-green-700 border-green-200 font-semibold">
                <i class="fas fa-check-circle text-[10px]"></i> Aprobada y Finalizada
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-red-100 text-red-700 border-red-200 font-semibold">
                <i class="fas fa-times-circle text-[10px]"></i> Rechazada
            </span>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        function filtrar(tipo) {
            var filas = document.querySelectorAll('.fila-solicitud');
            filas.forEach(function(fila) {
                if (tipo === 'todas') {
                    fila.style.display = '';
                } else if (tipo === 'excepcion') {
                    fila.style.display = fila.getAttribute('data-excepcion') === '1' ? '' : 'none';
                } else {
                    fila.style.display = fila.getAttribute('data-estado') === tipo ? '' : 'none';
                }
            });
            document.querySelectorAll('.filtro-btn').forEach(function(btn) {
                btn.classList.remove('activo');
                btn.style.backgroundColor = '';
                btn.style.color = '';
                btn.style.borderColor = '';
            });
            var activo = document.querySelector('[data-filtro="' + tipo + '"]');
            activo.classList.add('activo');
            activo.style.backgroundColor = '#5D0A28';
            activo.style.color = '#fff';
            activo.style.borderColor = '#5D0A28';
        }
        filtrar('todas');
    </script>
@endsection
