@extends('layouts.app')

@section('title', 'Panel Docente')
@section('subtitle', 'Portal Docente')
@section('rol', 'Docente')

@section('content')
    <div class="container mx-auto mt-2 p-4">

        {{-- Encabezado --}}
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800">Bandeja de Solicitudes</h2>
                <p class="text-gray-500 italic text-sm mt-1">
                    Solicitudes de corrección de nota recibidas de tus estudiantes.
                </p>
            </div>
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
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead style="background-color: #5D0A28;">
                    <tr>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Estudiante</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Materia / Evaluación</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Nota Reclamada</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Fecha</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-right">Acción</th>
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
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $solicitud->estudiante_nombre }}</p>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $solicitud->estudiante_carnet }}</p>
                            </td>
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $solicitud->materia_nombre }}</p>
                                <p class="text-xs text-gray-500 uppercase font-semibold mt-0.5">
                                    {{ $solicitud->evaluacion }} &mdash; {{ $solicitud->ciclo }}
                                </p>
                                @if($solicitud->es_excepcion)
                                    <span class="inline-flex items-center gap-1 mt-1 bg-amber-100 text-amber-700 border border-amber-300 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                        <i class="fas fa-exclamation-circle"></i> Excepción
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-xl font-extrabold" style="color: #5D0A28;">{{ $solicitud->nota_actual }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @php
                                    $clases = [
                                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'rechazado_docente'     => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-200',
                                        'finalizado'            => 'bg-green-100  text-green-700  border-green-200',
                                        'requiere_evidencia'    => 'bg-purple-100 text-purple-700 border-purple-200',
                                    ];
                                    $etiquetas = [
                                        'pendiente_docente'     => 'Pendiente',
                                        'rechazado_docente'     => 'Rechazada',
                                        'pendiente_coordinador' => 'Aprobada → Coordinador',
                                        'rechazado_coordinador' => 'Rech. Coordinador',
                                        'pendiente_admin'       => 'En Admin. Académico',
                                        'finalizado'            => 'Finalizada',
                                        'requiere_evidencia'    => 'Esperando evidencia',
                                    ];
                                    $estadoKey = $solicitud->estado;
                                    $estilo    = $clases[$estadoKey]    ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                    $etiqueta  = $etiquetas[$estadoKey] ?? $estadoKey;
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border {{ $estilo }}">
                                    {{ $etiqueta }}
                                </span>
                            </td>
                            <td class="p-4 text-xs text-gray-500 font-medium">
                                {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex flex-col items-end gap-1.5">
                                    <a href="/docente/solicitud/{{ $solicitud->id }}"
                                        style="background-color: #5D0A28;"
                                        onmouseover="this.style.backgroundColor='#4A0820'"
                                        onmouseout="this.style.backgroundColor='#5D0A28'"
                                        class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                        <i class="fas fa-search"></i> Revisar
                                    </a>
                                    @if($solicitud->estado === 'finalizado')
                                        <a href="/docente/solicitud/{{ $solicitud->id }}/pdf"
                                            class="text-green-700 hover:text-green-900 font-bold text-xs uppercase tracking-widest transition hover:underline">
                                            <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No tienes solicitudes pendientes</p>
                                    <p class="text-sm mt-1 italic">Cuando un estudiante envíe una solicitud, aparecerá aquí.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
