<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Coordinador - UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Portal Coordinador</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">
                    Coordinador
                </span>
                <span class="font-medium text-sm">{{ Auth::user()->nombre }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-300 transition" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ALERTAS FLASH --}}
    <div class="container mx-auto mt-6 px-4">
        @if(session('error'))
            <div id="alerta-error" class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div id="alerta-exito" class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
    </div>

    <div class="container mx-auto mt-2 p-4">

        {{-- Encabezado --}}
        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-800">Bandeja de Solicitudes</h2>
            <p class="text-gray-500 italic text-sm mt-1">
                Solicitudes aprobadas por docentes de
                <span class="font-bold" style="color: #5D0A28;">{{ $facultad->nombre }}</span>
            </p>
        </div>

        {{-- TABLA --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead style="background-color: #5D0A28;">
                    <tr>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Estudiante</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Materia / Carrera</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Docente</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Nota Reclamada</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Fecha</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $solicitud)
                        <tr class="border-b hover:bg-gray-50 transition">

                            {{-- Estudiante --}}
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $solicitud->estudiante_nombre }}</p>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $solicitud->estudiante_carnet }}</p>
                            </td>

                            {{-- Materia / Carrera --}}
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $solicitud->materia_nombre }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $solicitud->carrera_nombre }}</p>
                                <p class="text-xs text-gray-400 uppercase font-semibold mt-0.5">
                                    {{ $solicitud->evaluacion }} &mdash; {{ $solicitud->ciclo }}
                                </p>
                            </td>

                            {{-- Docente --}}
                            <td class="p-4">
                                <p class="text-sm text-gray-700 font-medium">{{ $solicitud->docente_nombre }}</p>
                            </td>

                            {{-- Nota --}}
                            <td class="p-4 text-center">
                                <span class="text-xl font-extrabold" style="color: #5D0A28;">
                                    {{ $solicitud->nota_actual }}
                                </span>
                            </td>

                            {{-- Badge estado --}}
                            <td class="p-4 text-center">
                                @php
                                    $clases = [
                                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-200',
                                        'finalizado'            => 'bg-green-100  text-green-700  border-green-200',
                                    ];
                                    $etiquetas = [
                                        'pendiente_coordinador' => 'En revisión (Coordinador)',
                                        'rechazado_coordinador' => 'Rechazada por Coordinador',
                                        'pendiente_admin'       => 'En revisión (Admin)',
                                        'finalizado'            => 'Finalizada',
                                    ];
                                    $iconos = [
                                        'pendiente_coordinador' => 'fa-hourglass-half',
                                        'rechazado_coordinador' => 'fa-times-circle',
                                        'pendiente_admin'       => 'fa-hourglass-half',
                                        'finalizado'            => 'fa-check-circle',
                                    ];
                                    $estadoKey = $solicitud->estado;
                                    $estilo    = $clases[$estadoKey]    ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                    $etiqueta  = $etiquetas[$estadoKey] ?? $estadoKey;
                                    $icono     = $iconos[$estadoKey]    ?? 'fa-circle';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border whitespace-nowrap {{ $estilo }}">
                                    <i class="fas {{ $icono }} text-[10px]"></i>
                                    {{ $etiqueta }}
                                </span>
                            </td>

                            {{-- Fecha --}}
                            <td class="p-4 text-xs text-gray-500 font-medium">
                                {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                            </td>

                            {{-- Botón revisar --}}
                            <td class="p-4 text-right">
                                <a href="/coordinador/solicitud/{{ $solicitud->id }}"
                                    style="background-color: #5D0A28;"
                                    onmouseover="this.style.backgroundColor='#4A0820'"
                                    onmouseout="this.style.backgroundColor='#5D0A28'"
                                    class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                    <i class="fas fa-search"></i> Revisar
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No hay solicitudes pendientes</p>
                                    <p class="text-sm mt-1 italic">Las solicitudes aprobadas por los docentes aparecerán aquí.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function desvanecerAlerta(id) {
                var el = document.getElementById(id);
                if (!el) return;
                setTimeout(function () {
                    el.style.opacity    = '0';
                    el.style.transform  = 'translateY(-10px)';
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    setTimeout(function () { el.remove(); }, 500);
                }, 3000);
            }
            desvanecerAlerta('alerta-error');
            desvanecerAlerta('alerta-exito');
        });
    </script>

</body>
</html>