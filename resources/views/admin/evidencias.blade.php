@extends('layouts.app')

@section('title', 'Evidencias')
@section('subtitle', 'Panel Admin. Académico')
@section('rol', 'Admin. Académico')

@section('content')
    <div class="container mx-auto mt-2 p-4">

        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800">Archivos de Evidencia</h2>
                <p class="text-gray-500 italic text-sm mt-1">
                    Registro de todos los archivos almacenados en el sistema.
                </p>
            </div>
            <a href="/admin/dashboard"
                class="px-5 py-3 rounded-lg text-sm font-bold uppercase tracking-wide border-2 border-gray-300 text-gray-600 hover:bg-gray-100 transition inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        {{-- Resumen --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-4 border-l-4" style="border-color: #5D0A28;">
                <p class="text-xs font-bold text-gray-400 uppercase">Total Archivos</p>
                <p class="text-2xl font-extrabold" style="color: #5D0A28;">{{ $evidencias->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Por Estudiantes</p>
                <p class="text-2xl font-extrabold text-blue-600">{{ $evidencias->where('rol_usuario', 'estudiante')->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Por Docentes</p>
                <p class="text-2xl font-extrabold text-green-600">{{ $evidencias->where('rol_usuario', 'docente')->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border-l-4 border-amber-500">
                <p class="text-xs font-bold text-gray-400 uppercase">Por Admin/Coord.</p>
                <p class="text-2xl font-extrabold text-amber-600">{{ $evidencias->whereIn('rol_usuario', ['admin', 'coordinador'])->count() }}</p>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead style="background-color: #5D0A28;">
                    <tr>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">ID</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Archivo</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Subido por</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Solicitud</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Descripción</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Fecha</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evidencias as $ev)
                    @php
                        $nombreArchivo = basename($ev->archivo);
                        $extension = strtoupper(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
                        $iconoArchivo = in_array($extension, ['PDF']) ? 'fa-file-pdf text-red-500' : 'fa-file-image text-blue-500';
                        $rolesClases = [
                            'estudiante'  => 'bg-orange-100 text-orange-700 border-orange-200',
                            'docente'     => 'bg-blue-100 text-blue-700 border-blue-200',
                            'coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'admin'       => 'bg-green-100 text-green-700 border-green-200',
                        ];
                        $rolClase = $rolesClases[$ev->rol_usuario] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                    @endphp
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-4 text-sm font-mono text-gray-500">{{ $ev->id }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $iconoArchivo }} text-lg"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 truncate max-w-[200px]" title="{{ $nombreArchivo }}">{{ $nombreArchivo }}</p>
                                    <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600">{{ $extension }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <p class="text-sm font-bold text-gray-800">{{ $ev->subido_por }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $rolClase }}">
                                {{ ucfirst($ev->rol_usuario) }}
                            </span>
                        </td>
                        <td class="p-4">
                            <a href="/admin/solicitud/{{ $ev->solicitud_id }}" class="font-bold text-sm hover:underline" style="color: #5D0A28;">
                                #{{ $ev->solicitud_id }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $ev->materia_nombre }}</p>
                            <p class="text-xs text-gray-400">{{ $ev->evaluacion }}</p>
                        </td>
                        <td class="p-4 text-sm text-gray-600 max-w-[200px] truncate" title="{{ $ev->descripcion }}">
                            {{ $ev->descripcion ?? '—' }}
                        </td>
                        <td class="p-4 text-xs text-gray-500 font-medium">
                            {{ \Carbon\Carbon::parse($ev->fecha)->format('d/m/Y H:i') }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('evidencia.ver', $ev->id) }}" target="_blank"
                                    style="background-color: #5D0A28;"
                                    class="text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition hover:opacity-90 inline-flex items-center gap-1">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="{{ route('evidencia.descargar', $ev->id) }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide border-2 border-gray-300 text-gray-600 hover:bg-gray-100 transition inline-flex items-center gap-1">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="flex flex-col items-center text-gray-400">
                                <i class="fas fa-folder-open text-5xl mb-4 text-gray-300"></i>
                                <p class="font-semibold text-base">No hay evidencias almacenadas</p>
                                <p class="text-sm mt-1 italic">Los archivos aparecerán aquí cuando se adjunten a solicitudes.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evidencias->count())
        <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
            <p class="text-sm text-blue-700">
                <i class="fas fa-database text-blue-500 mr-1"></i>
                <span class="font-bold">Almacenamiento:</span>
                Todos los archivos se guardan en Google Cloud Storage (bucket: <span class="font-mono font-bold">sistema-correccion-evidencias</span>)
                y se registran en la base de datos con su metadata (usuario, fecha, solicitud asociada).
            </p>
        </div>
        @endif

    </div>
@endsection
