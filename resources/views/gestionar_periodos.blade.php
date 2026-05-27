<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Periodos — UTEC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen pb-12">

    {{-- ===================================================
        NAVBAR
        Identifica al administrador logueado y ofrece el
        botón de logout (POST con CSRF) en la esquina derecha.
    =================================================== --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Panel Administrador</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">Admin</span>
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

    {{-- ===================================================
        ALERTAS FLASH
        Tres bloques de mensajes:
          - session('error')  → rojo, viene del controlador con ->with('error',...)
          - session('success')→ verde, viene de ->with('success',...)
          - $errors->any()    → rojo, lista los errores de validación del request
    =================================================== --}}
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
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-r-lg shadow-md text-sm">
                @foreach($errors->all() as $error)
                    <p><i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>

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

        {{-- ===================================================
            TABLA DE PERIODOS
            Cada fila renderiza un formulario independiente que
            envía un POST a /admin/periodos/{id}/actualizar.
            Eso permite editar cada periodo sin afectar a los demás.
        =================================================== --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: #5D0A28;">
                <h3 class="text-white font-bold text-base uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i> Periodos Registrados
                </h3>
                <span class="text-white text-xs font-semibold opacity-80">
                    Total: {{ $periodos->count() }}
                </span>
            </div>

            <table class="w-full text-left border-collapse">
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
                                {{-- Evaluación (solo lectura) --}}
                                <td class="p-3">
                                    <p class="font-bold text-gray-800 text-sm">{{ $periodo->evaluacion }}</p>
                                </td>

                                {{-- Ciclo (solo lectura) --}}
                                <td class="p-3">
                                    <p class="text-sm text-gray-600">{{ $periodo->ciclo_nombre }}</p>
                                </td>

                                {{-- Fecha de inicio (editable) --}}
                                <td class="p-3">
                                    <input type="date" name="fecha_inicio"
                                        value="{{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('Y-m-d') }}"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition"
                                        required>
                                </td>

                                {{-- Fecha de fin (editable) --}}
                                <td class="p-3">
                                    <input type="date" name="fecha_fin"
                                        value="{{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('Y-m-d') }}"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-[#5D0A28] outline-none transition"
                                        required>
                                </td>

                                {{-- Estado (editable)
                                     1 = Abierto (acepta solicitudes)
                                     0 = Cerrado (no acepta solicitudes aunque la fecha esté en rango)
                                     Solo aparece como activo en el flujo del estudiante si
                                     ESTADO=1 Y la fecha de hoy está dentro de fecha_inicio/fecha_fin --}}
                                <td class="p-3 text-center">
                                    <select name="estado"
                                        class="border-2 border-gray-200 rounded-lg p-2 text-sm font-bold focus:border-[#5D0A28] outline-none transition cursor-pointer">
                                        <option value="1" {{ (int)$periodo->estado === 1 ? 'selected' : '' }}
                                            class="text-green-700">Abierto</option>
                                        <option value="0" {{ (int)$periodo->estado === 0 ? 'selected' : '' }}
                                            class="text-red-700">Cerrado</option>
                                    </select>
                                </td>

                                {{-- Botón guardar --}}
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
                un periodo se considera <span class="font-bold">activo</span> únicamente cuando
                su estado es <span class="font-bold">Abierto</span> y la fecha actual está dentro del rango.
            </p>
        </div>

    </div>

    <script>
        // Auto-desvanecimiento de las alertas flash:
        // a los 3 segundos baja la opacidad y desliza el elemento hacia arriba,
        // y después de la transición lo elimina del DOM.
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
