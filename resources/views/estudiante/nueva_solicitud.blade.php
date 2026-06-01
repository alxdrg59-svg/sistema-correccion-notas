{{-- =====================================================
    VISTA: NUEVA SOLICITUD DE CORRECCION DE NOTA

    Esta vista permite al estudiante crear una solicitud de
    correccion de nota. Tiene dos modos de funcionamiento:

    1) MODO NORMAL ($modoExcepcion = false):
       El periodo de correccion esta activo. El estudiante
       puede crear una solicitud normal y, opcionalmente,
       marcar el checkbox de excepcion para solicitar
       correccion de la evaluacion anterior.

    2) MODO EXCEPCION FORZADO ($modoExcepcion = true):
       No hay periodo activo (todos vencieron). El checkbox
       de excepcion esta marcado y no se puede desmarcar.
       La evidencia se vuelve obligatoria.

    Variables que recibe del controlador:
       - $datosEstudiante: carrera y facultad del estudiante
       - $materias: lista de materias con docente y seccion
       - $periodoActivo: periodo activo o ultimo periodo vencido
       - $modoExcepcion: true si no hay periodo activo
       - $evaluacionAnterior: nombre de la evaluacion anterior
         (null si es Evaluacion 1 y no hay anterior)
===================================================== --}}
@extends('layouts.app')

@section('title', 'Nueva Solicitud')
@section('subtitle', 'Nueva Solicitud')
@section('body-class', 'pb-10')
@section('nav-class', 'mb-8')

@section('nav-right')
    <a href="/estudiante/dashboard" class="hover:text-red-300 transition text-sm font-medium flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Volver al Panel
    </a>
@endsection

@section('content')
    <div class="container mx-auto max-w-4xl px-4">

        {{-- ALERTA DE MODO EXCEPCION FORZADO --}}
        @if($modoExcepcion)
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 mb-6 rounded-r-lg shadow-md font-medium text-sm flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 text-lg"></i>
            <div>
                <p class="font-bold">El periodo de corrección ha finalizado.</p>
                <p class="mt-1">Esta solicitud se enviará como <span class="font-bold">excepción</span>. La evidencia es obligatoria.</p>
            </div>
        </div>
        @endif

        {{-- FORMULARIO DE SOLICITUD --}}
        <form action="/estudiante/guardar-solicitud" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-2xl rounded-xl p-8 border-t-8" style="border-color: #5D0A28;">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- DATOS DEL SOLICITANTE --}}
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

                    {{-- PERIODO DE EVALUACION --}}
                    <div class="sm:col-span-2 mt-2">
                        <label class="block text-xs font-bold uppercase mb-1 tracking-wider" style="color: #5D0A28;">
                            {{ $modoExcepcion ? 'Ultimo Periodo Registrado' : 'Periodo de Evaluación Activo' }}
                        </label>

                        <input type="text"
                            value="{{ $periodoActivo->evaluacion ?? 'Evaluación Activa' }}"
                            class="w-full p-3 border-2 rounded-lg outline-none text-sm font-bold shadow-sm"
                            style="background-color: #fff5f7; border-color: #5D0A28; color: #5D0A28;"
                            readonly>

                        @if(isset($periodoActivo->fecha_inicio) && isset($periodoActivo->fecha_fin))
                            <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                                <i class="fas fa-calendar-alt" style="color: #5D0A28;"></i>
                                {{ $modoExcepcion ? 'Periodo finalizado:' : 'Periodo activo:' }}
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

                {{-- CHECKBOX DE EXCEPCION --}}
                @if($evaluacionAnterior || $modoExcepcion)
                <div class="sm:col-span-2 bg-amber-50 p-4 rounded-lg border border-amber-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="es_excepcion" id="check_excepcion" value="1"
                            class="w-5 h-5 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                            {{ $modoExcepcion ? 'checked disabled' : '' }}>
                        @if($modoExcepcion)
                            <input type="hidden" name="es_excepcion" value="1">
                        @endif
                        <span class="text-sm font-bold text-amber-800 uppercase tracking-wide">
                            <i class="fas fa-exclamation-circle mr-1"></i> Solicitud por Excepción
                        </span>
                    </label>
                    <p class="text-xs text-amber-600 mt-2 ml-8 italic">
                        @if($modoExcepcion)
                            El periodo ha finalizado. Esta solicitud se enviará automáticamente como excepción.
                        @else
                            Marque esta casilla si desea solicitar corrección para una evaluación anterior cuyo periodo ya finalizó.
                        @endif
                    </p>

                    {{-- DROPDOWN DE EVALUACION ANTERIOR --}}
                    <div id="contenedor_eval_excepcion" class="{{ $modoExcepcion ? '' : 'hidden' }} mt-4 ml-8">
                        <label class="block text-xs font-bold text-amber-800 uppercase mb-1">Evaluación a Corregir</label>
                        <select name="evaluacion_excepcion" id="select_eval_excepcion"
                            class="w-full p-3 border-2 border-amber-200 rounded-lg outline-none focus:border-amber-500 transition bg-white text-sm font-bold text-gray-700">
                            <option value="{{ $evaluacionAnterior ?? '' }}">
                                {{ str_replace('Evaluacion', 'Evaluación', $evaluacionAnterior ?? '') }}
                            </option>
                        </select>
                        <p class="text-xs text-amber-500 mt-1 italic">
                            La corrección por excepción aplica para la evaluación anterior.
                        </p>
                    </div>
                </div>
                @endif

                {{-- SELECCION DE MATERIA --}}
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

                {{-- SECCION Y DOCENTE --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Sección</label>
                    <input type="text" id="input_seccion_visible"
                        placeholder="Se asignará automáticamente..."
                        class="w-full p-3 border-2 rounded-lg bg-gray-100 outline-none font-bold text-gray-700"
                        readonly>
                    <input type="hidden" name="seccion" id="input_seccion_hidden">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Docente que imparte la materia</label>
                    <input type="text" id="input_docente_nombre"
                        placeholder="Se asignará automáticamente..."
                        class="w-full p-3 border-2 rounded-lg bg-gray-100 outline-none"
                        readonly>
                    <input type="hidden" name="docente_id" id="input_docente_id">
                </div>

                {{-- NOTA A PROPONER --}}
                <div id="contenedor_nota" class="sm:col-span-2 p-4 rounded-lg border bg-gray-50 border-gray-200 transition-colors duration-300">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Nota a Proponer (0.0 a 10)</label>
                    <input type="number" step="0.1" min="0.0" max="10"
                        name="nota_actual" id="input_nota"
                        class="w-full p-3 border-2 rounded-lg outline-none bg-white"
                        style="focus-border-color: #5D0A28;"
                        placeholder="Ej: 7.5"
                        required>
                    <p id="error_nota" class="text-red-600 text-sm font-bold mt-2 hidden">
                        La nota no puede ser mayor a 10 ni menor a 0. Revise el valor ingresado.
                    </p>
                </div>

                {{-- MOTIVO DEL RECLAMO --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Motivo del Reclamo</label>
                    <textarea name="motivo" rows="4"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        placeholder="Explique detalladamente por qué solicita la corrección..."
                        required></textarea>
                </div>

                {{-- EVIDENCIA (ARCHIVO) --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold uppercase mb-1" id="label_evidencia"
                        style="color: {{ $modoExcepcion ? '#dc2626' : '#374151' }};">
                        <i class="fas fa-paperclip mr-1"></i> EVIDENCIA
                        <span id="texto_evidencia_obligatoria" class="{{ $modoExcepcion ? '' : 'hidden' }} text-red-500 font-normal normal-case ml-1">
                            (Obligatorio para excepciones)
                        </span>
                        <span id="texto_evidencia_opcional" class="{{ $modoExcepcion ? 'hidden' : '' }} text-gray-400 font-normal normal-case ml-1">
                            (Opcional)
                        </span>
                    </label>

                    <div class="border-2 border-dashed rounded-lg p-6 text-center transition cursor-pointer"
                        id="zona_evidencia"
                        style="border-color: {{ $modoExcepcion ? '#fca5a5' : '#d1d5db' }};">
                        <i class="fas fa-cloud-upload-alt text-3xl mb-2" id="icono_evidencia" style="color: {{ $modoExcepcion ? '#f87171' : '#9ca3af' }};"></i>
                        <p class="text-sm text-gray-500">Haz clic o arrastra archivos aquí</p>
                        <p class="text-xs text-gray-400 mt-1 italic">
                            Formatos: JPG, PNG, PDF — Máximo 5MB por archivo — Puede agregar varios
                        </p>
                    </div>

                    <input type="file" id="input_evidencia_selector" accept=".jpg,.jpeg,.png,.pdf" multiple class="hidden">
                    <div id="contenedor_inputs_evidencia"></div>
                    <div id="lista_archivos" class="hidden mt-3 space-y-1.5"></div>
                </div>

            </div>

            {{-- BOTONES DE ENVIO --}}
            <div class="mt-8 flex space-x-4">
                <button type="submit" id="btn_enviar"
                    style="background-color: #5D0A28;"
                    onmouseover="this.style.backgroundColor='#4A0820'"
                    onmouseout="this.style.backgroundColor='#5D0A28'"
                    class="flex-1 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest">
                    <i class="fas fa-paper-plane mr-2"></i>
                    <span id="texto_btn_enviar">{{ $modoExcepcion ? 'Enviar Solicitud de Excepción' : 'Enviar Solicitud al Docente' }}</span>
                </button>
                <a href="/estudiante/dashboard"
                    class="px-8 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition uppercase text-sm flex items-center">
                    Cancelar
                </a>
            </div>

        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // 1) AUTOCOMPLETAR SECCION Y DOCENTE
        document.getElementById('select_materia').addEventListener('change', function () {
            var opcion     = this.options[this.selectedIndex];
            document.getElementById('input_seccion_visible').value = opcion.getAttribute('data-seccion')    || '';
            document.getElementById('input_seccion_hidden').value  = opcion.getAttribute('data-seccion')    || '';
            document.getElementById('input_docente_nombre').value  = opcion.getAttribute('data-docente')    || '';
            document.getElementById('input_docente_id').value      = opcion.getAttribute('data-docente-id') || '';
        });

        // 2) VALIDACION DE NOTA EN TIEMPO REAL
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

        // 3) LOGICA DEL CHECKBOX DE EXCEPCION
        var checkExcepcion = document.getElementById('check_excepcion');
        if (checkExcepcion && !checkExcepcion.disabled) {
            checkExcepcion.addEventListener('change', function () {
                var contenedorEval = document.getElementById('contenedor_eval_excepcion');
                var zonaEvidencia = document.getElementById('zona_evidencia');
                var textoObligatorio = document.getElementById('texto_evidencia_obligatoria');
                var textoOpcional = document.getElementById('texto_evidencia_opcional');
                var labelEvidencia = document.getElementById('label_evidencia');
                var textoBtn = document.getElementById('texto_btn_enviar');
                var iconoEv = document.getElementById('icono_evidencia');

                if (this.checked) {
                    contenedorEval.classList.remove('hidden');
                    zonaEvidencia.style.borderColor = '#fca5a5';
                    iconoEv.style.color = '#f87171';
                    textoObligatorio.classList.remove('hidden');
                    textoOpcional.classList.add('hidden');
                    labelEvidencia.style.color = '#dc2626';
                    textoBtn.textContent = 'Enviar Solicitud de Excepción';
                } else {
                    contenedorEval.classList.add('hidden');
                    zonaEvidencia.style.borderColor = '#d1d5db';
                    iconoEv.style.color = '#9ca3af';
                    textoObligatorio.classList.add('hidden');
                    textoOpcional.classList.remove('hidden');
                    labelEvidencia.style.color = '#374151';
                    textoBtn.textContent = 'Enviar Solicitud al Docente';
                }
            });
        }

        // 4) GESTOR DE ARCHIVOS DE EVIDENCIA
        var archivosAcumulados = [];
        var zonaEvidencia = document.getElementById('zona_evidencia');
        var inputSelector = document.getElementById('input_evidencia_selector');
        var listaDiv = document.getElementById('lista_archivos');
        var contenedorInputs = document.getElementById('contenedor_inputs_evidencia');
        var extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        var maxTamano = 5 * 1024 * 1024; // 5MB

        zonaEvidencia.addEventListener('click', function () {
            inputSelector.click();
        });

        inputSelector.addEventListener('change', function () {
            agregarArchivos(this.files);
            this.value = '';
        });

        zonaEvidencia.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.style.borderColor = '#5D0A28';
            this.style.backgroundColor = '#fff5f7';
        });

        zonaEvidencia.addEventListener('dragleave', function (e) {
            e.preventDefault();
            this.style.backgroundColor = '';
            var esExcepcion = checkExcepcion && checkExcepcion.checked;
            this.style.borderColor = esExcepcion ? '#fca5a5' : '#d1d5db';
        });

        zonaEvidencia.addEventListener('drop', function (e) {
            e.preventDefault();
            this.style.backgroundColor = '';
            var esExcepcion = checkExcepcion && checkExcepcion.checked;
            this.style.borderColor = esExcepcion ? '#fca5a5' : '#d1d5db';
            if (e.dataTransfer.files.length > 0) {
                agregarArchivos(e.dataTransfer.files);
            }
        });

        function agregarArchivos(fileList) {
            for (var i = 0; i < fileList.length; i++) {
                var archivo = fileList[i];
                var ext = archivo.name.split('.').pop().toLowerCase();

                if (extensionesPermitidas.indexOf(ext) === -1) {
                    alert('El archivo "' + archivo.name + '" no es un formato permitido. Solo JPG, PNG y PDF.');
                    continue;
                }
                if (archivo.size > maxTamano) {
                    alert('El archivo "' + archivo.name + '" supera los 5MB.');
                    continue;
                }
                var duplicado = false;
                for (var j = 0; j < archivosAcumulados.length; j++) {
                    if (archivosAcumulados[j].name === archivo.name && archivosAcumulados[j].size === archivo.size) {
                        duplicado = true;
                        break;
                    }
                }
                if (!duplicado) {
                    archivosAcumulados.push(archivo);
                }
            }
            renderizarLista();
            sincronizarInputs();
        }

        function eliminarArchivo(indice) {
            archivosAcumulados.splice(indice, 1);
            renderizarLista();
            sincronizarInputs();
        }

        function renderizarLista() {
            listaDiv.innerHTML = '';

            if (archivosAcumulados.length === 0) {
                listaDiv.classList.add('hidden');
                return;
            }

            listaDiv.classList.remove('hidden');

            var encabezado = document.createElement('div');
            encabezado.className = 'flex items-center justify-between';
            encabezado.innerHTML = '<p class="text-xs font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1.5">' +
                '<i class="fas fa-paperclip"></i> ' + archivosAcumulados.length +
                ' archivo' + (archivosAcumulados.length > 1 ? 's' : '') +
                ' adjunto' + (archivosAcumulados.length > 1 ? 's' : '') + '</p>' +
                '<button type="button" onclick="eliminarTodos()" class="text-xs text-red-500 hover:text-red-700 font-bold">' +
                '<i class="fas fa-trash-alt mr-1"></i>Quitar todos</button>';
            listaDiv.appendChild(encabezado);

            for (var i = 0; i < archivosAcumulados.length; i++) {
                var archivo = archivosAcumulados[i];
                var tamano = (archivo.size / 1024 / 1024).toFixed(2);
                var extension = archivo.name.split('.').pop().toUpperCase();
                var icono = extension === 'PDF' ? 'fa-file-pdf text-red-500' : 'fa-file-image text-blue-500';

                var fila = document.createElement('div');
                fila.className = 'flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm';
                fila.innerHTML = '<i class="fas ' + icono + ' text-lg"></i>' +
                    '<span class="font-medium text-gray-700 truncate flex-1">' + archivo.name + '</span>' +
                    '<span class="text-xs text-gray-400 font-mono whitespace-nowrap">' + tamano + ' MB</span>' +
                    '<span class="text-xs font-bold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600">' + extension + '</span>' +
                    '<button type="button" onclick="eliminarArchivo(' + i + ')" class="text-red-400 hover:text-red-600 ml-1" title="Quitar archivo">' +
                    '<i class="fas fa-times-circle"></i></button>';
                listaDiv.appendChild(fila);
            }
        }

        function eliminarTodos() {
            archivosAcumulados = [];
            renderizarLista();
            sincronizarInputs();
        }

        function sincronizarInputs() {
            contenedorInputs.innerHTML = '';
            for (var i = 0; i < archivosAcumulados.length; i++) {
                var dt = new DataTransfer();
                dt.items.add(archivosAcumulados[i]);
                var input = document.createElement('input');
                input.type = 'file';
                input.name = 'evidencias[]';
                input.files = dt.files;
                input.style.display = 'none';
                contenedorInputs.appendChild(input);
            }
        }
    </script>
@endsection
