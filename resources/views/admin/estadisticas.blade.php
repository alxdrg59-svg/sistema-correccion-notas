@extends('layouts.app')

@section('title', 'Estadísticas')
@section('subtitle', 'Panel Admin. Académico')
@section('rol', 'Admin. Académico')

@section('content')
    <div class="container mx-auto mt-2 p-4">

        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800">Estadísticas y Registros</h2>
                <p class="text-gray-500 italic text-sm mt-1">Información consolidada por facultad y búsqueda de estudiantes.</p>
            </div>
            <a href="/admin/dashboard"
                class="px-5 py-3 rounded-lg text-sm font-bold uppercase tracking-wide border-2 border-gray-300 text-gray-600 hover:bg-gray-100 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        {{-- Tabs --}}
        @php $tabActiva = $tabActiva ?? 'estadisticas'; @endphp
        <div class="flex gap-2 mb-6">
            <button onclick="mostrarTab('estadisticas')" id="tab-estadisticas"
                class="tab-btn px-5 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wide transition border-2">
                <i class="fas fa-chart-bar mr-1"></i> Estadísticas por Facultad
            </button>
            <button onclick="mostrarTab('busqueda')" id="tab-busqueda"
                class="tab-btn px-5 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wide transition border-2">
                <i class="fas fa-search mr-1"></i> Buscar Estudiante
            </button>
        </div>

        {{-- TAB: Estadísticas por Facultad --}}
        <div id="contenido-estadisticas" class="tab-contenido">
            {{-- Filtros --}}
            <div class="bg-white rounded-xl shadow-md p-5 mb-6">
                <form action="/admin/estadisticas" method="GET" class="flex flex-wrap items-end gap-4">
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
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="/admin/estadisticas"
                            class="px-5 py-2 rounded-lg text-sm font-bold uppercase tracking-wide border-2 border-gray-300 text-gray-500 hover:bg-gray-100 transition">
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabla de estadísticas --}}
            <div class="bg-white rounded-xl shadow-md overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead style="background-color: #5D0A28;">
                        <tr>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Facultad</th>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Estudiantes</th>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Total Solicitudes</th>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Pendientes</th>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Finalizadas</th>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Rechazadas</th>
                            <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Excepciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estadisticas as $stat)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-4 font-bold text-gray-800">{{ $stat->facultad_nombre }}</td>
                            <td class="p-4 text-center">
                                <span class="text-lg font-bold text-gray-600">{{ $estudiantesPorFacultad[$stat->facultad_id] ?? 0 }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-lg font-extrabold" style="color: #5D0A28;">{{ $stat->total }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">{{ $stat->pendientes }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">{{ $stat->finalizadas }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">{{ $stat->rechazadas }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">{{ $stat->excepciones }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-chart-bar text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No hay datos para mostrar</p>
                                    <p class="text-sm mt-1 italic">Ajusta los filtros o espera a que se registren solicitudes.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB: Buscar Estudiante --}}
        <div id="contenido-busqueda" class="tab-contenido hidden">
            <div class="bg-white rounded-xl shadow-md p-5 mb-6">
                <form action="/admin/buscar-estudiante" method="GET" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Buscar por nombre o carnet</label>
                        <input type="text" name="q" value="{{ $busqueda ?? '' }}"
                            placeholder="Ej: 2720162024 o Juan Pérez"
                            class="w-full border-2 border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-[#5D0A28]">
                    </div>
                    <button type="submit"
                        style="background-color: #5D0A28;"
                        class="text-white px-5 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wide transition hover:opacity-90">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                </form>
            </div>

            @if(isset($busqueda) && $busqueda)
                @if($estudiante)
                    {{-- Datos del estudiante --}}
                    <div class="bg-white rounded-xl shadow-md p-5 mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nombre</p>
                                <p class="text-gray-800 font-bold">{{ $estudiante->nombre }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Carnet</p>
                                <p class="text-gray-800 font-mono font-bold">{{ $estudiante->carnet }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Carrera</p>
                                <p class="text-gray-800 font-semibold">{{ $estudiante->carrera_nombre ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Facultad</p>
                                <p class="text-gray-800 font-semibold">{{ $estudiante->facultad_nombre ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de solicitudes del estudiante --}}
                    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead style="background-color: #5D0A28;">
                                <tr>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Materia</th>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Evaluación / Ciclo</th>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Docente</th>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Nota</th>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Estado</th>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Fecha</th>
                                    <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($solicitudes as $sol)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <p class="font-bold text-gray-800 text-sm">{{ $sol->materia_nombre }}</p>
                                        @if($sol->es_excepcion)
                                            <span class="inline-flex items-center gap-1 mt-1 bg-amber-100 text-amber-700 border border-amber-300 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                                <i class="fas fa-exclamation-circle"></i> Excepción
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <p class="text-xs font-bold text-gray-600 uppercase">{{ $sol->evaluacion }}</p>
                                        <p class="text-xs text-gray-400">{{ $sol->ciclo }}</p>
                                    </td>
                                    <td class="p-4 text-sm text-gray-700">{{ $sol->docente_nombre }}</td>
                                    <td class="p-4 text-center">
                                        <span class="text-xl font-extrabold" style="color: #5D0A28;">{{ $sol->nota_actual }}</span>
                                    </td>
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
                                                'pendiente_admin'       => 'Pendiente Admin',
                                                'finalizado'            => 'Finalizada',
                                                'requiere_evidencia'    => 'Esperando evidencia',
                                            ];
                                            $estilo = $clases[$sol->estado] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                            $etiqueta = $etiquetas[$sol->estado] ?? $sol->estado;
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $estilo }}">
                                            {{ $etiqueta }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-xs text-gray-500 font-medium">
                                        {{ \Carbon\Carbon::parse($sol->fecha_solicitud)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="/admin/solicitud/{{ $sol->id }}"
                                            style="background-color: #5D0A28;"
                                            class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition hover:opacity-90 inline-flex items-center gap-1.5">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center">
                                        <div class="flex flex-col items-center text-gray-400">
                                            <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                                            <p class="font-semibold text-base">Este estudiante no tiene solicitudes</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 text-center">
                        <i class="fas fa-user-slash text-4xl text-yellow-500 mb-3"></i>
                        <p class="font-bold text-yellow-800 text-lg">No se encontró ningún estudiante</p>
                        <p class="text-yellow-700 text-sm mt-1">Verifica el carnet o nombre ingresado: "{{ $busqueda }}"</p>
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection

@section('scripts')
<script>
    function mostrarTab(tab) {
        document.querySelectorAll('.tab-contenido').forEach(function(el) { el.classList.add('hidden'); });
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.style.backgroundColor = '';
            btn.style.color = '';
            btn.style.borderColor = '#d1d5db';
        });
        document.getElementById('contenido-' + tab).classList.remove('hidden');
        var btnActivo = document.getElementById('tab-' + tab);
        btnActivo.style.backgroundColor = '#5D0A28';
        btnActivo.style.color = '#fff';
        btnActivo.style.borderColor = '#5D0A28';
    }
    mostrarTab('{{ $tabActiva ?? "estadisticas" }}');
</script>
@endsection
