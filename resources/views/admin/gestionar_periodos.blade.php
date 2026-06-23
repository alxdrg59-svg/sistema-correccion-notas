@extends('layouts.app')

@section('title', 'Gestionar Periodos')
@section('subtitle', 'Panel Admin. Académico')
@section('rol', 'Admin. Académico')
@section('body-class', 'pb-12')

@section('content')
    <div class="container mx-auto mt-2 p-4">

        {{-- Volver --}}
        <a href="/admin/dashboard" style="color: #5D0A28;"
            class="inline-flex items-center font-bold text-sm hover:underline mb-4">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
        </a>

        {{-- Encabezado --}}
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-800">Gestionar Periodos de Corrección</h2>
            <p class="text-gray-500 italic text-sm mt-1">
                Modifica las fechas de inicio y fin, y abre o cierra los periodos durante los cuales se reciben solicitudes.
            </p>
        </div>

        {{-- CICLOS ACADÉMICOS --}}
        <div class="bg-white rounded-xl shadow-md overflow-x-auto mb-8">
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: #5D0A28;">
                <h3 class="text-white font-bold text-base uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-graduation-cap"></i> Ciclos Académicos
                </h3>
                <span class="text-white text-xs font-semibold opacity-80">
                    Total: {{ $ciclos->count() }}
                </span>
            </div>

            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Ciclo</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Fecha de Inicio</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Fecha de Fin</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-right">Guardar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ciclos as $ciclo)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <form action="/admin/ciclos/{{ $ciclo->id }}/actualizar" method="POST">
                                @csrf
                                <td class="p-3">
                                    <select name="nombre"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm font-bold text-gray-800 focus:border-[#5D0A28] outline-none transition cursor-pointer appearance-none bg-white bg-no-repeat bg-[length:12px] bg-[right_10px_center]"
                                        style="background-image: url('data:image/svg+xml,<%3Fxml version=%221.0%22%3F><svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22><path fill=%22%235D0A28%22 d=%22M7 10l5 5 5-5z%22/></svg>'); padding-right: 30px;">
                                        <option value="Ciclo 1" {{ $ciclo->nombre == 'Ciclo 1' ? 'selected' : '' }}>Ciclo 1</option>
                                        <option value="Ciclo 2" {{ $ciclo->nombre == 'Ciclo 2' ? 'selected' : '' }}>Ciclo 2</option>
                                        <option value="Ciclo 3" {{ $ciclo->nombre == 'Ciclo 3' ? 'selected' : '' }}>Ciclo 3</option>
                                    </select>
                                </td>
                                <td class="p-3">
                                    <input type="date" name="fecha_inicio"
                                        value="{{ $ciclo->fecha_inicio ? \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('Y-m-d') : '' }}"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition"
                                        required>
                                </td>
                                <td class="p-3">
                                    <input type="date" name="fecha_fin"
                                        value="{{ $ciclo->fecha_fin ? \Carbon\Carbon::parse($ciclo->fecha_fin)->format('Y-m-d') : '' }}"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition"
                                        required>
                                </td>
                                <td class="p-3 text-center">
                                    @php
                                        $hoy = now()->toDateString();
                                        $esCicloActual = $ciclo->fecha_inicio && $ciclo->fecha_fin && $ciclo->fecha_inicio <= $hoy && $ciclo->fecha_fin >= $hoy;
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                        {{ $esCicloActual ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $esCicloActual ? 'Ciclo Actual' : ($ciclo->fecha_inicio ? 'No activo' : 'Sin fechas') }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <button type="submit"
                                        style="background-color: #5D0A28;"
                                        onmouseover="this.style.backgroundColor='#4A0820'"
                                        onmouseout="this.style.backgroundColor='#5D0A28'"
                                        class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-graduation-cap text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No hay ciclos registrados</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLA DE PERIODOS --}}
        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: #5D0A28;">
                <h3 class="text-white font-bold text-base uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i> Periodos Registrados
                </h3>
                <span class="text-white text-xs font-semibold opacity-80">
                    Total: {{ $periodos->count() }}
                </span>
            </div>

            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Evaluación</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Ciclo</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Fecha de Inicio</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider">Fecha de Fin</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-3 font-bold text-gray-600 text-xs uppercase tracking-wider text-right">Guardar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodos as $periodo)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <form action="/admin/periodos/{{ $periodo->id }}/actualizar" method="POST">
                                @csrf
                                <td class="p-3">
                                    <p class="font-bold text-gray-800 text-sm">{{ $periodo->evaluacion }}</p>
                                </td>

                                <td class="p-3">
                                    <select name="ciclo_id"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition">
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ $periodo->ciclo_id == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="p-3">
                                    <input type="date" name="fecha_inicio"
                                        value="{{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('Y-m-d') }}"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition"
                                        required>
                                </td>

                                <td class="p-3">
                                    <input type="date" name="fecha_fin"
                                        value="{{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('Y-m-d') }}"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition"
                                        required>
                                </td>

                                {{-- Estado determinado automaticamente por las fechas --}}
                                <td class="p-3 text-center">
                                    @php
                                        $hoy = now()->toDateString();
                                        $activo = $periodo->fecha_inicio <= $hoy && $periodo->fecha_fin >= $hoy;
                                        $vencido = $periodo->fecha_fin < $hoy;
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                                        {{ $activo ? 'bg-green-100 text-green-700' : ($vencido ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ $activo ? 'Activo' : ($vencido ? 'Finalizado' : 'Programado') }}
                                    </span>
                                </td>

                                <td class="p-3 text-right">
                                    <button type="submit"
                                        style="background-color: #5D0A28;"
                                        onmouseover="this.style.backgroundColor='#4A0820'"
                                        onmouseout="this.style.backgroundColor='#5D0A28'"
                                        class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-calendar-times text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No hay periodos registrados</p>
                                    <p class="text-sm mt-1 italic">Crea los periodos directamente en la base de datos.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tip --}}
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
            <p class="text-sm text-blue-700">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                <span class="font-bold">Recuerda:</span>
                el sistema determina automáticamente el periodo activo según las fechas.
                Si la fecha de hoy está dentro del rango de un periodo, ese periodo está <span class="font-bold">Activo</span>.
                Si ya pasó, aparece como <span class="font-bold">Finalizado</span>.
                Si aún no llega, aparece como <span class="font-bold">Programado</span>.
            </p>
        </div>

    </div>
@endsection
