<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Estudiante - UTEC</title>
    {{-- Tailwind via CDN — en producción cambiar por compilado con npm --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- FontAwesome para los íconos de la tabla y navbar --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-100 min-h-screen">

    {{-- ===================================================
        NAVBAR
            Muestra el nombre del usuario, su rol (estudiante) y un botón de logout.
            El logout se hace con un formulario POST por seguridad (CSRF).
    =================================================== --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Portal Académico</h1>
            </div>
            <div class="flex items-center space-x-4">
                {{-- Badge de rol --}}
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">
                    Estudiante
                </span>
                {{-- Nombre del usuario desde la sesión --}}
                <span class="font-medium text-sm">
                    Bienvenido, {{ Auth::user()->nombre }}
                </span>
                {{-- Logout: usa POST por seguridad (CSRF) --}}
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-300 transition-colors duration-200" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ===================================================
        ALERTAS FLASH
        Se muestran cuando el controlador redirige con
        ->with('success', ...) o ->with('error', ...)
        se desvanecen solos a los 3 segundos (ver script)
    =================================================== --}}
    <div class="container mx-auto mt-6 px-4">

        @if(session('error'))
            <div id="alerta-error"
                class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div id="alerta-exito"
                class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

    </div>

    {{-- ===================================================
        CONTENIDO PRINCIPAL - TABLA DE SOLICITUDES
        Muestra todas las solicitudes del estudiante logueado
        con su estado, fecha y enlace a detalles.
    =================================================== --}}
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

        {{-- ===================================================
            TABLA DE SOLICITUDES
            Muestra todas las solicitudes del estudiante logueado
            con su estado, fecha y enlace a detalles.
        =================================================== --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Materia / Evaluación</th>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider">Fecha de Solicitud</th>
                        <th class="p-4 font-bold text-gray-600 text-xs uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @forelse muestra filas si hay datos, o el @empty si la colección está vacía --}}
                    @forelse($solicitudes as $solicitud)
                        <tr class="border-b hover:bg-gray-50 transition">

                            {{-- Nombre de materia via relación Eloquent + evaluación y ciclo --}}
                            <td class="p-4">
                                <p class="font-bold text-gray-800">
                                    {{ $solicitud->materiaRelacion->nombre ?? 'Sin nombre de materia' }}
                                </p>
                                <p class="text-xs text-gray-500 uppercase font-semibold tracking-tight mt-0.5">
                                    {{ $solicitud->evaluacion ?? '—' }} &mdash; {{ $solicitud->ciclo ?? '—' }}
                                </p>
                            </td>

                            {{-- ===============================================
                                BADGE DE ESTADO
                                Mapea el ENUM real de la BD a colores y textos
                                legibles para el estudiante. Si llega un valor
                                inesperado cae al estilo gris por defecto (??)
                            =============================================== --}}
                            <td class="p-4 text-center">
                                @php
                                    // Colores Tailwind por cada valor del ENUM
                                    $clases = [
                                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'rechazado_docente'     => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-200',
                                        'finalizado'            => 'bg-green-100  text-green-700  border-green-200',
                                    ];

                                    // Ícono FontAwesome por estado
                                    $iconos = [
                                        'pendiente_docente'     => 'fa-hourglass-half',
                                        'rechazado_docente'     => 'fa-times-circle',
                                        'pendiente_coordinador' => 'fa-hourglass-half',
                                        'rechazado_coordinador' => 'fa-times-circle',
                                        'pendiente_admin'       => 'fa-hourglass-half',
                                        'finalizado'            => 'fa-check-circle',
                                    ];

                                    // Texto amigable en lugar del valor crudo del ENUM
                                    // Distinguimos quién rechazó la solicitud para que el estudiante sepa la etapa
                                    $etiquetas = [
                                        'pendiente_docente'     => 'En revisión (Docente)',
                                        'rechazado_docente'     => 'Rechazada por Docente',
                                        'pendiente_coordinador' => 'En revisión (Coordinador)',
                                        'rechazado_coordinador' => 'Rechazada por Coordinador',
                                        'pendiente_admin'       => 'En revisión (Admin)',
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

                            {{-- ===============================================
                                FECHA DE SOLICITUD
                                Formatea la fecha con Carbon para mostrar día/mes/año
                                y hora. Si no hay fecha (nueva solicitud) muestra "Reciente"
                            =============================================== --}}
                            <td class="p-4 text-sm text-gray-500 font-medium">
                                @if($solicitud->fecha_solicitud)
                                    {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                                @else
                                    <span class="italic text-gray-400">Reciente</span>
                                @endif
                            </td>

                            {{-- Acciones: ver detalle siempre; descargar PDF solo si la
                                 solicitud ya está finalizada (es decir, tiene constancia). --}}
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-3 justify-end flex-wrap">
                                    <a href="/estudiante/solicitud/{{ $solicitud->id }}"
                                        style="color: #5D0A28;"
                                        onmouseover="this.style.color='#4A0820'"
                                        onmouseout="this.style.color='#5D0A28'"
                                        class="hover:underline font-bold text-xs uppercase tracking-widest transition">
                                        <i class="fas fa-eye mr-1"></i> Ver Detalles
                                    </a>

                                    @if($solicitud->estado === 'finalizado')
                                        <a href="/estudiante/solicitud/{{ $solicitud->id }}/pdf"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5"
                                            title="Descargar constancia oficial en PDF">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        {{-- Mensaje cuando el estudiante no tiene ninguna solicitud aún --}}
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
                <i class="fas fa-hourglass-half text-[10px]"></i> En revisión (Admin)
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-green-100 text-green-700 border-green-200 font-semibold">
                <i class="fas fa-check-circle text-[10px]"></i> Aprobada y Finalizada
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border bg-red-100 text-red-700 border-red-200 font-semibold">
                <i class="fas fa-times-circle text-[10px]"></i> Rechazada por Docente o Coordinador
            </span>
        </div>

    </div>

    {{-- ===================================================
        SCRIPT: Auto-desvanecimiento de alertas
            Busca los elementos por ID y aplica una transición de opacidad
            y movimiento hacia arriba antes de removerlos del DOM. 
            
    =================================================== --}}
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