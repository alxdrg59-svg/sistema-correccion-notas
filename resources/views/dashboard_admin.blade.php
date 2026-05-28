<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Panel Administrador</h1>
            </div>
            <div class="flex items-center space-x-4">
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
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-800">Panel de Solicitudes</h2>
                <p class="text-gray-500 italic text-sm mt-1">
                    Todas las solicitudes de corrección de nota del sistema.
                </p>
            </div>
            <a href="/admin/periodos"
                style="background-color: #5D0A28;"
                onmouseover="this.style.backgroundColor='#4A0820'"
                onmouseout="this.style.backgroundColor='#5D0A28'"
                class="text-white px-5 py-3 rounded-lg text-sm font-bold uppercase tracking-wide transition inline-flex items-center gap-2 shadow-lg">
                <i class="fas fa-calendar-alt"></i> Gestionar Periodos
            </a>
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

        {{-- Filtros --}}
        <div class="mb-4 flex flex-wrap gap-2" id="filtros">
            <button onclick="filtrar('todas')" class="filtro-btn activo px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="todas">Todas</button>
            <button onclick="filtrar('pendiente')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="pendiente">Pendientes</button>
            <button onclick="filtrar('rechazada')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="rechazada">Rechazadas</button>
            <button onclick="filtrar('finalizado')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="finalizado">Finalizadas</button>
            <button onclick="filtrar('excepcion')" class="filtro-btn px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wide border transition" data-filtro="excepcion">Excepciones</button>
        </div>

        {{-- TABLA --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
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
                                    ];
                                    $etiquetas = [
                                        'pendiente_docente'     => 'Pendiente Docente',
                                        'rechazado_docente'     => 'Rechazada',
                                        'pendiente_coordinador' => 'Pendiente Coordinador',
                                        'rechazado_coordinador' => 'Rechazada',
                                        'pendiente_admin'       => 'Pendiente Admin',
                                        'finalizado'            => 'Finalizada',
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

</body>
</html>