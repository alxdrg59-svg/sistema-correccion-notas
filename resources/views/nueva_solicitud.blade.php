<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud - UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 pb-10">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white mb-8 shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Nueva Solicitud</h1>
            </div>
            <a href="/estudiante/dashboard" class="hover:text-red-300 transition text-sm font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container mx-auto max-w-4xl px-4">
        <form action="/estudiante/guardar-solicitud" method="POST"
            class="bg-white shadow-2xl rounded-xl p-8 border-t-8" style="border-color: #5D0A28;">
            @csrf

            <div class="grid grid-cols-2 gap-6">

                {{-- ===============================================
                    DATOS DEL SOLICITANTE
                =============================================== --}}
                <div class="col-span-2 bg-gray-50 p-4 rounded-lg border grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 uppercase font-bold mb-1">Datos del Solicitante</p>
                        <p class="text-lg font-bold" style="color: #5D0A28;">{{ Auth::user()->nombre }}</p>
                        <p class="text-sm text-gray-500">
                            Carnet: <span class="font-mono font-bold text-gray-800">{{ Auth::user()->carnet ?? 'Sin Carnet' }}</span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Facultad</label>
                        <input type="text"
                            value="{{ $datosEstudiante->facultad_nombre ?? 'No asignada' }}"
                            class="w-full p-2.5 border rounded-lg bg-gray-100 text-gray-600 outline-none text-sm font-medium"
                            readonly>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Carrera</label>
                        <input type="text"
                            value="{{ $datosEstudiante->carrera_nombre ?? 'No asignada' }}"
                            class="w-full p-2.5 border rounded-lg bg-gray-100 text-gray-600 outline-none text-sm font-medium"
                            readonly>
                    </div>

                    {{-- ===============================================
                        PERIODO DE EVALUACIÓN ACTIVO
                        Se muestra el periodo activo para que el estudiante sepa a qué evaluación corresponde su solicitud.
                        Si no hay un periodo activo, se muestra un mensaje genérico.
                    =============================================== --}}

                    {{-- ===============================================
                        EVALUACIÓN A RECLAMAR
                        El controlador puede mandar uno o dos periodos:
                          - $periodoActivo:   siempre llega (el abierto hoy)
                          - $periodoAnterior: opcional, la evaluación anterior
                                              dentro del mismo ciclo
                        Si solo viene el activo, mostramos un input readonly
                        + un hidden con su id. Si viene el anterior también,
                        renderizamos un <select> para que el estudiante elija
                        cuál de las dos evaluaciones reclamar.
                    =============================================== --}}
                    <div class="col-span-2 mt-2">
                        <label class="block text-xs font-bold uppercase mb-1 tracking-wider" style="color: #5D0A28;">
                            Evaluación a Reclamar
                        </label>

                        {{-- Caso 1: solo hay periodo activo → input bloqueado + hidden con su id --}}
                        @if(!isset($periodoAnterior) || !$periodoAnterior)
                            <input type="text"
                                value="{{ $periodoActivo->evaluacion ?? 'Evaluación Activa' }}"
                                class="w-full p-3 border-2 rounded-lg outline-none text-sm font-bold shadow-sm"
                                style="background-color: #fff5f7; border-color: #5D0A28; color: #5D0A28;"
                                readonly>
                            <input type="hidden" name="periodo_id" value="{{ $periodoActivo->id }}">

                        @else
                            {{-- Caso 2: existe el periodo anterior → select con dos opciones.
                                 Los atributos data-inicio / data-fin sirven al JS al final
                                 del archivo para actualizar el texto "Periodo de recepción"
                                 cada vez que cambia la selección. --}}
                            <select name="periodo_id" id="select_periodo" required
                                class="w-full p-3 border-2 rounded-lg outline-none text-sm font-bold shadow-sm cursor-pointer"
                                style="background-color: #fff5f7; border-color: #5D0A28; color: #5D0A28;">
                                <option value="{{ $periodoActivo->id }}"
                                    data-inicio="{{ $periodoActivo->fecha_inicio }}"
                                    data-fin="{{ $periodoActivo->fecha_fin }}">
                                    {{ $periodoActivo->evaluacion }} (Activa)
                                </option>
                                <option value="{{ $periodoAnterior->id }}"
                                    data-inicio="{{ $periodoAnterior->fecha_inicio }}"
                                    data-fin="{{ $periodoAnterior->fecha_fin }}">
                                    {{ $periodoAnterior->evaluacion }} (Excepción — evaluación anterior)
                                </option>
                            </select>
                        @endif

                        {{-- ===============================================
                            FECHAS DEL PERIODO ACTIVO
                            Si hay un periodo activo, se muestran sus fechas debajo.
                            Con el select, se actualizan en tiempo real al cambiar de evaluación.
                        =============================================== --}}

                        @if(isset($periodoActivo->fecha_inicio) && isset($periodoActivo->fecha_fin))
                            <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                                <i class="fas fa-calendar-alt" style="color: #5D0A28;"></i>
                                Periodo de recepción de solicitudes:
                                <span id="periodo_inicio" class="font-bold text-gray-700">
                                    {{ \Carbon\Carbon::parse($periodoActivo->fecha_inicio)->format('d/m/Y') }}
                                </span>
                                <span class="text-gray-400">—</span>
                                <span id="periodo_fin" class="font-bold text-gray-700">
                                    {{ \Carbon\Carbon::parse($periodoActivo->fecha_fin)->format('d/m/Y') }}
                                </span>
                            </p>
                        @endif

                        @if(isset($periodoAnterior) && $periodoAnterior)
                            <p class="text-xs text-amber-700 mt-1.5 flex items-start gap-1.5 bg-amber-50 border border-amber-200 rounded p-2">
                                <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                                <span>
                                    <span class="font-bold">Excepción disponible:</span>
                                    También puedes reclamar la nota de la evaluación inmediatamente anterior a la actual.
                                </span>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- ===============================================
                SELECCIÓN DE MATERIA
                El estudiante selecciona la materia para la cual solicita la corrección.
                El select se llena dinámicamente con las materias que el estudiante tiene inscritas,
                traídas desde la base de datos. Al seleccionar una materia, se autocompletan los campos de sección y docente.
                =============================================== --}}
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Materia Objeto de Corrección</label>
                    <select name="materia_id" id="select_materia"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        required>
                        <option value="" data-docente="" data-docente-id="" data-seccion="">
                            Seleccione la Materia...
                        </option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->materia_id }}"
                                data-docente="{{ $materia->docente_nombre }}"
                                data-docente-id="{{ $materia->docente_id }}"
                                data-seccion="{{ $materia->estudiante_seccion }}">
                                {{ $materia->materia_nombre }} — {{ ucfirst($materia->estudiante_modalidad) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ===============================================
                SECCIÓN
                Se autocompleta al seleccionar la materia, es solo informativo para el estudiante.
                =============================================== --}}

                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Sección</label>
                    <input type="text" id="input_seccion_visible"
                        placeholder="Se asignará automáticamente..."
                        class="w-full p-3 border-2 rounded-lg bg-gray-100 outline-none font-bold text-gray-700"
                        readonly>
                    <input type="hidden" name="seccion" id="input_seccion_hidden">
                </div>

                {{-- ===============================================
                DOCENTE QUE IMPARTE LA MATERIA
                Se autocompleta al seleccionar la materia, es solo informativo para el estudiante.
                =============================================== --}}

                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Docente que imparte la materia</label>
                    <input type="text" id="input_docente_nombre"
                        placeholder="Se asignará automáticamente..."
                        class="w-full p-3 border-2 rounded-lg bg-gray-100 outline-none"
                        readonly>
                    <input type="hidden" name="docente_id" id="input_docente_id">
                </div>

                {{-- ── NOTA A RECLAMAR ── --}}
                <div id="contenedor_nota" class="col-span-2 p-4 rounded-lg border bg-gray-50 border-gray-200 transition-colors duration-300">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Nota a Reclamar (0.0 a 9.9)</label>
                    <p class="text-xs text-gray-500 mb-2 italic">Ingrese la nota que el docente le publicó y que usted considera incorrecta.</p>
                    <input type="number" step="0.1" min="0.0" max="9.9"
                        name="nota_actual" id="input_nota"
                        class="w-full p-3 border-2 rounded-lg outline-none bg-white"
                        style="focus-border-color: #5D0A28;"
                        placeholder="Ej: 7.5"
                        required>
                    <p id="error_nota" class="text-red-600 text-sm font-bold mt-2 hidden">
                        ⚠️ La nota no puede ser mayor a 9.9 ni menor a 0.0. Revise el valor ingresado.
                    </p>
                </div>

                {{-- ── MOTIVO ── --}}
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Motivo del Reclamo</label>
                    <textarea name="motivo" rows="4"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        placeholder="Explique detalladamente por qué solicita la corrección..."
                        required></textarea>
                </div>

            </div>

            {{-- ===============================================
                BOTONES DE ENVÍO
                El botón de enviar se deshabilita si la nota ingresada es inválida (mayor a 9.9 o menor a 0).
        =============================================== --}}

            <div class="mt-8 flex space-x-4">
                <button type="submit" id="btn_enviar"
                    style="background-color: #5D0A28;"
                    onmouseover="this.style.backgroundColor='#4A0820'"
                    onmouseout="this.style.backgroundColor='#5D0A28'"
                    class="flex-1 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Solicitud al Docente
                </button>
                <a href="/estudiante/dashboard"
                    class="px-8 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition uppercase text-sm flex items-center">
                    Cancelar
                </a>
            </div>
                
            @if(session('error'))
        <div id="alerta-error" 
            class="container mx-auto max-w-4xl px-4 mt-4">
                <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-r-lg shadow-md font-medium text-sm flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span>{{ session('error') }}</span>
            </div>
        </div>
            @endif
        </form>
    </div>

    <script>
        // Autocompletado de sección y docente al seleccionar materia
        document.getElementById('select_materia').addEventListener('change', function () {
            var opcion     = this.options[this.selectedIndex];
            var docNombre  = opcion.getAttribute('data-docente');
            var docId      = opcion.getAttribute('data-docente-id');
            var seccion    = opcion.getAttribute('data-seccion');

            document.getElementById('input_seccion_visible').value = seccion    || '';
            document.getElementById('input_seccion_hidden').value  = seccion    || '';
            document.getElementById('input_docente_nombre').value  = docNombre  || '';
            document.getElementById('input_docente_id').value      = docId      || '';
        });

        // Sincronización del bloque "Periodo de recepción" con el select de evaluación.
        // El select solo existe cuando hay una evaluación anterior disponible
        // (la "excepción"). Cuando el estudiante cambia entre la activa y la anterior,
        // se actualizan los <span> de fechas leyendo los atributos data-inicio/data-fin
        // de la opción seleccionada y se reformatean al formato d/m/Y.
        var selectPeriodo = document.getElementById('select_periodo');
        if (selectPeriodo) {
            selectPeriodo.addEventListener('change', function () {
                var opcion = this.options[this.selectedIndex];
                var inicio = opcion.getAttribute('data-inicio');
                var fin    = opcion.getAttribute('data-fin');

                // Convierte "2026-01-15" → "15/01/2026"
                function fmt(fechaIso) {
                    if (!fechaIso) return '';
                    var partes = fechaIso.substring(0, 10).split('-');
                    return partes[2] + '/' + partes[1] + '/' + partes[0];
                }

                document.getElementById('periodo_inicio').textContent = fmt(inicio);
                document.getElementById('periodo_fin').textContent    = fmt(fin);
            });
        }

        // Validación de nota en tiempo real
        document.getElementById('input_nota').addEventListener('input', function () {
            var valor      = parseFloat(this.value);
            var btnSubmit  = document.getElementById('btn_enviar');
            var errorMsg   = document.getElementById('error_nota');
            var contenedor = document.getElementById('contenedor_nota');

            if (valor > 9.9 || valor < 0 || isNaN(valor)) {
                errorMsg.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnSubmit.style.opacity = '0.5';
                btnSubmit.style.cursor  = 'not-allowed';
                contenedor.style.backgroundColor = '#fef2f2';
                contenedor.style.borderColor      = '#fca5a5';
                this.style.borderColor            = '#ef4444';
            } else {
                errorMsg.classList.add('hidden');
                btnSubmit.disabled = false;
                btnSubmit.style.opacity = '1';
                btnSubmit.style.cursor  = 'pointer';
                contenedor.style.backgroundColor = '';
                contenedor.style.borderColor      = '';
                this.style.borderColor            = '';
            }
        });
    </script>
</body>
</html>