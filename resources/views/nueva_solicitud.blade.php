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
        <div class="container mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC <span class="hidden sm:inline">— Nueva Solicitud</span></h1>
            </div>
            <a href="/estudiante/dashboard" class="hover:text-red-300 transition text-sm font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container mx-auto max-w-4xl px-4">
        <form action="/estudiante/guardar-solicitud" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-2xl rounded-xl p-8 border-t-8" style="border-color: #5D0A28;">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- ===============================================
                    DATOS DEL SOLICITANTE
                =============================================== --}}
                <div class="sm:col-span-2 bg-gray-50 p-4 rounded-lg border grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
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

                    <div class="sm:col-span-2 mt-2">
                        <label class="block text-xs font-bold uppercase mb-1 tracking-wider" style="color: #5D0A28;">
                            Periodo de Evaluación Activo
                        </label>

                        <input type="text"
                            value="{{ $periodoActivo->evaluacion ?? 'Evaluación Activa' }}"
                            class="w-full p-3 border-2 rounded-lg outline-none text-sm font-bold shadow-sm"
                            style="background-color: #fff5f7; border-color: #5D0A28; color: #5D0A28;"
                            readonly>

                    {{-- ===============================================
                        FECHAS DEL PERIODO ACTIVO
                        Si hay un periodo activo, se muestran sus fechas de inicio y fin debajo del nombre de la evaluación.
                    =============================================== --}}

                        @if(isset($periodoActivo->fecha_inicio) && isset($periodoActivo->fecha_fin))
                            <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                                <i class="fas fa-calendar-alt" style="color: #5D0A28;"></i>
                                Periodo activo:
                                <span class="font-bold text-gray-700">
                                    {{ \Carbon\Carbon::parse($periodoActivo->fecha_inicio)->format('d/m/Y') }}
                                </span>
                                <span class="text-gray-400">—</span>
                                <span class="font-bold text-gray-700">
                                    {{ \Carbon\Carbon::parse($periodoActivo->fecha_fin)->format('d/m/Y') }}
                                </span>
                            </p>
                        @endif

                        @if(isset($periodoActivo->id))
                            <input type="hidden" name="periodo_id" value="{{ $periodoActivo->id }}">
                        @endif
                    </div>
                </div>

                {{-- ===============================================
                SELECCIÓN DE MATERIA
                El estudiante selecciona la materia para la cual solicita la corrección.
                El select se llena dinámicamente con las materias que el estudiante tiene inscritas,
                traídas desde la base de datos. Al seleccionar una materia, se autocompletan los campos de sección y docente.
                =============================================== --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Materia Sujeta a Corrección</label>
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

                {{-- ── NOTA ── --}}
                <div id="contenedor_nota" class="sm:col-span-2 p-4 rounded-lg border bg-gray-50 border-gray-200 transition-colors duration-300">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Nota a Proponer (0.0 a 10)</label>
                    <input type="number" step="0.1" min="0.0" max="10"
                        name="nota_actual" id="input_nota"
                        class="w-full p-3 border-2 rounded-lg outline-none bg-white"
                        style="focus-border-color: #5D0A28;"
                        placeholder="Ej: 7.5"
                        required>
                    <p id="error_nota" class="text-red-600 text-sm font-bold mt-2 hidden">
                        ⚠️ La nota no puede ser mayor a 10 ni menor a 0. Revise el valor ingresado.
                    </p>
                </div>

                {{-- ── MOTIVO ── --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Motivo del Reclamo</label>
                    <textarea name="motivo" rows="4"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        placeholder="Explique detalladamente por qué solicita la corrección..."
                        required></textarea>
                </div>

                {{-- ── EVIDENCIA ── --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-red-600 uppercase mb-1">
                        <i class="fas fa-paperclip mr-1"></i> EVIDENCIA
                        <span class="text-red-500 font-normal normal-case ml-1">(Adjuntar evidencia es obligatorio para excepciones)</span>
                    </label>
                    <input type="file" name="evidencia" accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full p-2 border-2 border-red-300 rounded-lg bg-white text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-red-100 file:text-red-700 hover:file:bg-red-200 transition">
                    <p class="text-xs text-red-500 mt-1">Formatos: JPG, PNG, PDF — Maximo 5MB</p>
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

        // Validación de nota en tiempo real
        document.getElementById('input_nota').addEventListener('input', function () {
            var valor      = parseFloat(this.value);
            var btnSubmit  = document.getElementById('btn_enviar');
            var errorMsg   = document.getElementById('error_nota');
            var contenedor = document.getElementById('contenedor_nota');

            if (valor > 10 || valor < 0 || isNaN(valor)) {
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