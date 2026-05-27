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

    {{-- ===================================================
        NAVBAR
        Acceso directo a "Gestionar Periodos" (vista que permite
        modificar fechas y estado de los periodos de corrección),
        nombre del admin logueado y botón de logout (POST + CSRF).
    =================================================== --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Panel Administrador</h1>
            </div>
            <div class="flex items-center space-x-4">
                {{-- Acceso rápido a gestionar periodos --}}
                <a href="/admin/periodos"
                    class="bg-white/10 hover:bg-white/20 transition px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wide flex items-center gap-2"
                    title="Gestionar periodos de corrección">
                    <i class="fas fa-calendar-alt"></i> Gestionar Periodos
                </a>
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
        <div class="mb-6">
            <h2 class="text-3xl font-extrabold text-gray-800">Bandeja de Solicitudes</h2>
            <p class="text-gray-500 italic text-sm mt-1">
                Todas las solicitudes del sistema: pendientes de cierre, finalizadas y rechazadas.
            </p>
        </div>

        {{-- ===================================================
            CONTADORES RESUMEN
            Cuatro tarjetas con los conteos calculados en el controlador:
              - Pendientes:  solicitudes en pendiente_admin
              - Finalizadas: solicitudes con la corrección ya aplicada
              - Rechazadas:  rechazadas por docente o coordinador
              - Total:       todas las solicitudes que ve el admin
        =================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pendientes</p>
                <p class="text-2xl font-extrabold text-blue-700 mt-1">{{ $contadores['pendientes'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Finalizadas</p>
                <p class="text-2xl font-extrabold text-green-700 mt-1">{{ $contadores['finalizadas'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rechazadas</p>
                <p class="text-2xl font-extrabold text-red-700 mt-1">{{ $contadores['rechazadas'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-gray-500">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total</p>
                <p class="text-2xl font-extrabold text-gray-700 mt-1">{{ $contadores['total'] }}</p>
            </div>
        </div>

        {{-- ===================================================
            FILTROS DE LA TABLA (solo cliente, no recarga)
            Cada botón guarda el estado a filtrar en data-filtro.
            El script al final del archivo recorre las filas de la
            tabla y muestra/oculta las que no coincidan con el filtro.
        =================================================== --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-bold text-gray-500 uppercase mr-2">Filtrar:</span>
            <button type="button" class="btn-filtro px-3 py-1.5 rounded-full border bg-gray-800 text-white font-semibold" data-filtro="todos">
                Todos
            </button>
            <button type="button" class="btn-filtro px-3 py-1.5 rounded-full border bg-white text-blue-700 border-blue-300 font-semibold hover:bg-blue-50" data-filtro="pendiente_admin">
                Pendientes
            </button>
            <button type="button" class="btn-filtro px-3 py-1.5 rounded-full border bg-white text-green-700 border-green-300 font-semibold hover:bg-green-50" data-filtro="finalizado">
                Finalizadas
            </button>
            <button type="button" class="btn-filtro px-3 py-1.5 rounded-full border bg-white text-red-700 border-red-300 font-semibold hover:bg-red-50" data-filtro="rechazadas">
                Rechazadas
            </button>
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
                            // data-estado se usa como llave para el filtro del cliente.
                            // Las dos variantes de rechazo se unifican bajo "rechazadas"
                            // para que el botón "Rechazadas" muestre ambos casos.
                            $tipoFila = in_array($solicitud->estado, ['rechazado_docente', 'rechazado_coordinador'])
                                ? 'rechazadas'
                                : $solicitud->estado;
                        @endphp
                        <tr class="fila-solicitud border-b hover:bg-gray-50 transition" data-estado="{{ $tipoFila }}">

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
                            </td>

                            {{-- ===============================================
                                BADGE DE ESTADO
                                Mapea el valor crudo del ENUM (pendiente_docente,
                                rechazado_coordinador, etc.) a un color de fondo
                                de Tailwind, un texto legible y un icono FontAwesome.
                                Si llegara un valor desconocido se cae al estilo gris.
                            =============================================== --}}
                            <td class="p-4 text-center">
                                @php
                                    $clases = [
                                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-200',
                                        'rechazado_docente'     => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-200',
                                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-200',
                                        'finalizado'            => 'bg-green-100  text-green-700  border-green-200',
                                    ];
                                    $etiquetas = [
                                        'pendiente_docente'     => 'En revisión (Docente)',
                                        'rechazado_docente'     => 'Rechazada por Docente',
                                        'pendiente_coordinador' => 'En revisión (Coordinador)',
                                        'rechazado_coordinador' => 'Rechazada por Coordinador',
                                        'pendiente_admin'       => 'Pendiente de Cierre',
                                        'finalizado'            => 'Finalizada',
                                    ];
                                    $iconos = [
                                        'pendiente_docente'     => 'fa-hourglass-half',
                                        'rechazado_docente'     => 'fa-times-circle',
                                        'pendiente_coordinador' => 'fa-hourglass-half',
                                        'rechazado_coordinador' => 'fa-times-circle',
                                        'pendiente_admin'       => 'fa-hourglass-half',
                                        'finalizado'            => 'fa-check-circle',
                                    ];
                                    $estilo   = $clases[$solicitud->estado]    ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                    $etiqueta = $etiquetas[$solicitud->estado] ?? $solicitud->estado;
                                    $icono    = $iconos[$solicitud->estado]    ?? 'fa-circle';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border whitespace-nowrap {{ $estilo }}">
                                    <i class="fas {{ $icono }} text-[10px]"></i>
                                    {{ $etiqueta }}
                                </span>
                            </td>

                            {{-- Fecha --}}
                            <td class="p-4 text-xs text-gray-500 font-medium whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                            </td>

                            {{-- ===============================================
                                BOTÓN DE ACCIÓN
                                Solo las solicitudes en pendiente_admin permiten
                                "Aplicar Corrección" (formulario para registrar la
                                nota nueva). Para los demás estados se muestra un
                                botón secundario "Ver Detalle" de solo lectura.
                            =============================================== --}}
                            <td class="p-4 text-right">
                                <div class="inline-flex items-center gap-2 justify-end flex-wrap">
                                    @if($solicitud->estado === 'pendiente_admin')
                                        {{-- Acción primaria: aplicar corrección de nota --}}
                                        <a href="/admin/solicitud/{{ $solicitud->id }}"
                                            style="background-color: #5D0A28;"
                                            onmouseover="this.style.backgroundColor='#4A0820'"
                                            onmouseout="this.style.backgroundColor='#5D0A28'"
                                            class="text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5">
                                            <i class="fas fa-edit"></i> Aplicar Corrección
                                        </a>
                                    @else
                                        {{-- Acción secundaria: ver detalle (lectura) --}}
                                        <a href="/admin/solicitud/{{ $solicitud->id }}"
                                            class="border-2 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5 hover:bg-gray-100"
                                            style="color: #5D0A28; border-color: #5D0A28;">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                    @endif

                                    {{-- Botón de descarga PDF: solo aparece para solicitudes
                                         ya finalizadas, que son las que tienen constancia. --}}
                                    @if($solicitud->estado === 'finalizado')
                                        <a href="/admin/solicitud/{{ $solicitud->id }}/pdf"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-1.5"
                                            title="Descargar constancia en PDF">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-4 text-gray-300"></i>
                                    <p class="font-semibold text-base">No hay solicitudes registradas</p>
                                    <p class="text-sm mt-1 italic">Cuando se reciban solicitudes en el sistema, aparecerán aquí.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    {{-- Fila placeholder cuando se filtra y no hay coincidencias --}}
                    <tr id="fila-sin-coincidencias" class="hidden">
                        <td colspan="9" class="p-12 text-center">
                            <div class="flex flex-col items-center text-gray-400">
                                <i class="fas fa-filter text-5xl mb-4 text-gray-300"></i>
                                <p class="font-semibold text-base">No hay solicitudes en este filtro</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        // Toda la lógica del cliente vive aquí:
        //   1) Auto-desvanecer las alertas flash a los 3 segundos
        //   2) Filtro por estado de las filas de la tabla
        document.addEventListener('DOMContentLoaded', function () {
            // -------- Auto-desvanecimiento de alertas --------
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

            // -------- Filtro por estado --------
            // Toma todos los botones de filtro y todas las filas de la tabla.
            // Al hacer click en un botón:
            //   - se marca como activo cambiándole el fondo
            //   - se recorren las filas y se ocultan las que no coinciden con
            //     el valor de data-filtro (excepto cuando el filtro es "todos")
            //   - si no queda ninguna fila visible, se muestra la fila placeholder
            var botones = document.querySelectorAll('.btn-filtro');
            var filas   = document.querySelectorAll('.fila-solicitud');
            var sinCoincidencias = document.getElementById('fila-sin-coincidencias');

            botones.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var filtro = this.getAttribute('data-filtro');

                    // Resaltar el botón clickeado y devolver los otros a su estilo neutro
                    botones.forEach(function (b) {
                        b.classList.remove('bg-gray-800', 'text-white');
                        b.classList.add('bg-white');
                    });
                    this.classList.add('bg-gray-800', 'text-white');
                    this.classList.remove('bg-white');

                    // Mostrar / ocultar filas según el filtro elegido
                    var visibles = 0;
                    filas.forEach(function (fila) {
                        var estado = fila.getAttribute('data-estado');
                        if (filtro === 'todos' || estado === filtro) {
                            fila.classList.remove('hidden');
                            visibles++;
                        } else {
                            fila.classList.add('hidden');
                        }
                    });

                    // Mostrar el placeholder "sin coincidencias" solo si quedaron 0 filas visibles
                    if (sinCoincidencias) {
                        if (visibles === 0 && filas.length > 0) {
                            sinCoincidencias.classList.remove('hidden');
                        } else {
                            sinCoincidencias.classList.add('hidden');
                        }
                    }
                });
            });
        });
    </script>

</body>
</html>
