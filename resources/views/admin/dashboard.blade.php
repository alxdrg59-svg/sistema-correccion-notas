@extends('layouts.app')

@section('title', 'Panel Admin. Académico')
@section('subtitle', 'Panel Admin. Académico')
@section('rol', 'Admin. Académico')

@section('content')
    <div class="container mx-auto mt-2 p-4">

        {{-- Encabezado --}}
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800">Panel de Solicitudes</h2>
                <p class="text-gray-500 italic text-sm mt-1">
                    Todas las solicitudes de corrección de nota del sistema.
                </p>
            </div>
            <div class="flex gap-3">
                <a href="/admin/estadisticas"
                    class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-3 rounded-lg text-sm font-bold uppercase tracking-wide transition inline-flex items-center gap-2 shadow-lg">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </a>
                <a href="/admin/periodos"
                    style="background-color: #5D0A28;"
                    onmouseover="this.style.backgroundColor='#4A0820'"
                    onmouseout="this.style.backgroundColor='#5D0A28'"
                    class="text-white px-5 py-3 rounded-lg text-sm font-bold uppercase tracking-wide transition inline-flex items-center gap-2 shadow-lg">
                    <i class="fas fa-calendar-alt"></i> Gestionar Periodos
                </a>
            </div>
        </div>

        {{-- Tarjetas resumen --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Pendientes</p>
                <p class="text-2xl font-extrabold text-blue-600">{{ $contadores['pendientes'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Finalizadas</p>
                <p class="text-2xl font-extrabold text-green-600">{{ $contadores['finalizadas'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-red-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Rechazadas</p>
                <p class="text-2xl font-extrabold text-red-600">{{ $contadores['rechazadas'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4" style="border-color: #5D0A28;">
                <p class="text-xs font-bold text-gray-400 uppercase">Total</p>
                <p class="text-2xl font-extrabold" style="color: #5D0A28;">{{ $contadores['total'] }}</p>
            </div>
        </div>

        {{-- Barra de filtros: estado (client-side) + botón filtros avanzados --}}
        @php
            $filtrosActivos = request('facultad_id') || request('evaluacion') || request('ciclo') || request('anio');
            $numFiltros = (request('facultad_id') ? 1 : 0) + (request('evaluacion') ? 1 : 0) + (request('ciclo') ? 1 : 0) + (request('anio') ? 1 : 0);
        @endphp
        <div class="mb-4 flex flex-wrap items-center gap-2" id="filtros">
            <button onclick="toggleFiltrosAvanzados()" id="btn-filtros-avanzados"
                class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition inline-flex items-center gap-1.5 {{ $filtrosActivos ? 'text-white border-[#5D0A28]' : 'border-gray-300 text-gray-500 hover:bg-gray-100' }}"
                style="{{ $filtrosActivos ? 'background-color: #5D0A28;' : '' }}">
                <i class="fas fa-sliders-h"></i> Filtros
                @if($numFiltros > 0)
                    <span class="bg-white text-[#5D0A28] rounded-full w-5 h-5 flex items-center justify-center text-[10px] font-extrabold">{{ $numFiltros }}</span>
                @endif
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button onclick="filtrar('todas')" class="filtro-btn activo px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="todas">Todas</button>
            <button onclick="filtrar('pendiente')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="pendiente">Pendientes</button>
            <button onclick="filtrar('rechazada')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="rechazada">Rechazadas</button>
            <button onclick="filtrar('finalizado')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="finalizado">Finalizadas</button>
            <button onclick="filtrar('excepcion')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="excepcion">Excepciones</button>
        </div>

        {{-- Panel de filtros avanzados (colapsable) --}}
        <div id="panel-filtros-avanzados" class="{{ $filtrosActivos ? '' : 'hidden' }} mb-4">
            <div class="bg-white rounded-xl shadow-md p-5 border-l-4" style="border-color: #5D0A28;">
                <form action="/admin/dashboard" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Facultad</label>
                        <select name="facultad_id" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#5D0A28]">
                            <option value="">Todas</option>
                            @foreach($facultades as $fac)
                                <option value="{{ $fac->id }}" {{ request('facultad_id') == $fac->id ? 'selected' : '' }}>{{ $fac->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Evaluación</label>
                        <select name="evaluacion" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#5D0A28]">
                            <option value="">Todas</option>
                            @foreach($evaluaciones as $eval)
                                <option value="{{ $eval }}" {{ request('evaluacion') == $eval ? 'selected' : '' }}>{{ $eval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Ciclo</label>
                        <select name="ciclo" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#5D0A28]">
                            <option value="">Todos</option>
                            @foreach($ciclos as $cic)
                                <option value="{{ $cic }}" {{ request('ciclo') == $cic ? 'selected' : '' }}>{{ $cic }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Año</label>
                        <select name="anio" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#5D0A28]">
                            <option value="">Todos</option>
                            @foreach($anios as $a)
                                <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            style="background-color: #5D0A28;"
                            class="text-white px-5 py-2 rounded-lg text-sm font-bold uppercase tracking-wide transition hover:opacity-90">
                            <i class="fas fa-filter mr-1"></i> Aplicar
                        </button>
                        <a href="/admin/dashboard"
                            class="px-5 py-2 rounded-lg text-sm font-bold uppercase tracking-wide border-2 border-gray-300 text-gray-500 hover:bg-gray-100 transition">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead style="background-color: #5D0A28;">
                    <tr>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Estudiante</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Materia</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Facultad / Carrera</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Docente</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Nota Reclamada</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Evaluación</th>
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

                            {{-- Estudiante --}}
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $solicitud->estudiante_nombre }}</p>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $solicitud->estudiante_carnet }}</p>
                            </td>

                            {{-- Materia --}}
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $solicitud->materia_nombre }}</p>
                            </td>

                            {{-- Facultad / Carrera --}}
                            <td class="p-4">
                                <p class="text-sm text-gray-700 font-semibold">{{ $solicitud->facultad_nombre }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $solicitud->carrera_nombre }}</p>
                            </td>

                            {{-- Docente --}}
                            <td class="p-4">
                                <p class="text-sm text-gray-700">{{ $solicitud->docente_nombre }}</p>
                            </td>

                            {{-- Nota reclamada --}}
                            <td class="p-4 text-center">
                                <span class="text-2xl font-extrabold" style="color: #5D0A28;">
                                    {{ $solicitud->nota_actual }}
                                </span>
                            </td>

                            {{-- Evaluación --}}
                            <td class="p-4">
                                <p class="text-xs font-bold text-gray-600 uppercase">{{ $solicitud->evaluacion }}</p>
                                <p class="text-xs text-gray-400">{{ $solicitud->ciclo }}</p>
                                @if($solicitud->es_excepcion)
                                    <span class="inline-flex items-center gap-1 mt-1 bg-amber-100 text-amber-700 border border-amber-300 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                        <i class="fas fa-exclamation-circle"></i> Excepción
                                    </span>
                                @endif
                            </td>

                            {{-- Estado --}}
                            <td class="p-4 text-center">
                                @php
                                    $clases = [
                                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'rechazado_docente'     => 'bg-red-100 text-red-700 border-red-200',
                                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'rechazado_coordinador' => 'bg-red-100 text-red-700 border-red-200',
                                        'pendiente_admin'       => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'finalizado'            => 'bg-green-100 text-green-700 border-green-200',
                                        'requiere_evidencia'    => 'bg-purple-100 text-purple-700 border-purple-200',
                                    ];
                                    $etiquetas = [
                                        'pendiente_docente'     => 'Pendiente Docente',
                                        'rechazado_docente'     => 'Rechazada',
                                        'pendiente_coordinador' => 'Pendiente Coordinador',
                                        'rechazado_coordinador' => 'Rechazada',
                                        'pendiente_admin'       => 'Pendiente Admin. Académico',
                                        'finalizado'            => 'Finalizada',
                                        'requiere_evidencia'    => 'Esperando evidencia',
                                    ];
                                    $estilo = $clases[$solicitud->estado] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                    $etiqueta = $etiquetas[$solicitud->estado] ?? $solicitud->estado;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $estilo }}">
                                    {{ $etiqueta }}
                                </span>
                            </td>

                            {{-- Fecha --}}
                            <td class="p-4 text-xs text-gray-500 font-medium">
                                {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                            </td>

                            {{-- Botón --}}
                            <td class="p-4 text-right">
                                <a href="/admin/solicitud/{{ $solicitud->id }}"
                                    style="background-color: #5D0A28;"
                                    onmouseover="this.style.backgroundColor='#4A0820'"
                                    onmouseout="this.style.backgroundColor='#5D0A28'"
                                    class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                    @if($solicitud->estado === 'pendiente_admin')
                                        <i class="fas fa-edit"></i> Aplicar Corrección
                                    @else
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    @endif
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No hay solicitudes registradas</p>
                                    <p class="text-sm mt-1 italic">Cuando se procesen solicitudes, aparecerán aquí.</p>
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

        function toggleFiltrosAvanzados() {
            var panel = document.getElementById('panel-filtros-avanzados');
            panel.classList.toggle('hidden');
        }
    </script>
@endsection
