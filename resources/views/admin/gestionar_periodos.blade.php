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
            <h2 class="text-3xl font-extrabold text-gray-800">Gestionar Periodos</h2>
            <p class="text-gray-500 italic text-sm mt-1">
                Selecciona el ciclo académico activo y modifica las fechas de los periodos de corrección.
            </p>
        </div>

        {{-- CICLO ACADÉMICO ACTIVO --}}
        @php
            $cicloActivo = $ciclos->firstWhere('estado', 'activo');
        @endphp
        <div class="bg-white rounded-xl shadow-md mb-8">
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: #5D0A28;">
                <h3 class="text-white font-bold text-base uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-graduation-cap"></i> Ciclo Académico
                </h3>
            </div>

            <form action="/admin/ciclos/actualizar" method="POST">
                @csrf
                <div class="px-6 py-5 flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-3">
                        <label class="font-bold text-gray-600 text-sm uppercase tracking-wider">Ciclo</label>
                        <select name="ciclo_id"
                            class="border-2 border-gray-200 rounded-lg px-4 py-2.5 text-sm font-bold text-gray-800 focus:border-[#5D0A28] outline-none transition cursor-pointer">
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}" {{ $ciclo->estado == 'activo' ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                        {{ $cicloActivo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $cicloActivo ? 'Activo: ' . $cicloActivo->nombre : 'Sin ciclo activo' }}
                    </span>

                    <div class="ml-auto">
                        <button type="submit"
                            style="background-color: #5D0A28;"
                            onmouseover="this.style.backgroundColor='#4A0820'"
                            onmouseout="this.style.backgroundColor='#5D0A28'"
                            class="text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
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
                                    <p class="font-semibold text-gray-700 text-sm">{{ $periodo->ciclo_nombre }}</p>
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
