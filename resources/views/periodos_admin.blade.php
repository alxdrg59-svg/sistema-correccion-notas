<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Periodos - UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Gestionar Periodos</h1>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/admin/dashboard" class="hover:text-red-300 transition text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Volver al Panel
                </a>
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">
                    Admin
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

        <div class="mb-8">
            <h2 class="text-3xl font-extrabold text-gray-800">Periodos de Corrección</h2>
            <p class="text-gray-500 italic text-sm mt-1">
                Active o desactive los periodos de evaluación para permitir que los estudiantes envíen solicitudes de corrección de notas.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead style="background-color: #5D0A28;">
                    <tr>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Evaluación</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Descripción</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Ciclo</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Fecha Inicio</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider">Fecha Fin</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-center">Estado</th>
                        <th class="p-4 font-bold text-white text-xs uppercase tracking-wider text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periodos as $periodo)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-4">
                                <p class="font-bold text-gray-800 text-sm">{{ $periodo->evaluacion }}</p>
                            </td>
                            <td class="p-4">
                                <p class="text-sm text-gray-600">{{ $periodo->descripcion ?? '-' }}</p>
                            </td>
                            <td class="p-4">
                                <p class="text-sm text-gray-700 font-medium">{{ $periodo->ciclo_nombre }}</p>
                            </td>
                            <td class="p-4 text-sm text-gray-600 font-medium">
                                {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }}
                            </td>
                            <td class="p-4 text-sm text-gray-600 font-medium">
                                {{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}
                            </td>
                            <td class="p-4 text-center">
                                @if($periodo->estado == 1)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase">
                                        Activo
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-xs font-bold uppercase">
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <form action="/admin/periodos/{{ $periodo->id }}/toggle" method="POST" class="inline">
                                    @csrf
                                    @if($periodo->estado == 1)
                                        <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                            <i class="fas fa-toggle-off"></i> Desactivar
                                        </button>
                                    @else
                                        <button type="submit"
                                            style="background-color: #5D0A28;"
                                            onmouseover="this.style.backgroundColor='#4A0820'"
                                            onmouseout="this.style.backgroundColor='#5D0A28'"
                                            class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                            <i class="fas fa-toggle-on"></i> Activar
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
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
