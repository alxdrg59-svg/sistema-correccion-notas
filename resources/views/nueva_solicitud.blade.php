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

    {{-- NAVBAR: Barra de navegacion con boton para volver al panel --}}
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

        {{-- ===============================================
            ALERTA DE MODO EXCEPCION FORZADO
            Solo se muestra cuando no hay periodo activo.
            Avisa al estudiante que su solicitud sera
            enviada como excepcion y que la evidencia
            es obligatoria.
        =============================================== --}}
        @if($modoExcepcion)
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 mb-6 rounded-r-lg shadow-md font-medium text-sm flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 text-lg"></i>
            <div>
                <p class="font-bold">El periodo de corrección ha finalizado.</p>
                <p class="mt-1">Esta solicitud se enviará como <span class="font-bold">excepción</span>. La evidencia es obligatoria.</p>
            </div>
        </div>
        @endif

        {{-- ===============================================
            FORMULARIO DE SOLICITUD
            Se envia por POST a /estudiante/guardar-solicitud
            con enctype multipart para permitir subir archivos
        =============================================== --}}
        {{-- Mostrar errores de validacion si los hay --}}
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 mb-4 rounded-r-lg shadow-md text-sm">
                <p class="font-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Por favor corrija los siguientes errores:</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Mostrar mensaje de error del controlador --}}
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="/estudiante/guardar-solicitud" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-2xl rounded-xl p-8 border-t-8" style="border-color: #5D0A28;">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                {{-- ===============================================
                    DATOS DEL SOLICITANTE
                    Muestra nombre, carnet, facultad y carrera del
                    estudiante logueado. Son campos de solo lectura.
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
                        PERIODO DE EVALUACION
                        Muestra el periodo activo o el ultimo periodo
                        que vencio (segun el modo). El titulo cambia:
                        - Modo normal: "Periodo de Evaluación Activo"
                        - Modo excepcion: "Ultimo Periodo Registrado"
                        El ID del periodo se envia como campo oculto
                        para que el controlador pueda buscarlo en la BD.
                    =============================================== --}}
                    <div class="sm:col-span-2 mt-2">
                        <label class="block text-xs font-bold uppercase mb-1 tracking-wider" style="color: #5D0A28;">
                            {{ $modoExcepcion ? 'Ultimo Periodo Registrado' : 'Periodo de Evaluación Activo' }}
                        </label>

                        <input type="text"
                            value="{{ $periodoActivo->evaluacion ?? 'Evaluación Activa' }}"
                            class="w-full p-3 border-2 rounded-lg outline-none text-sm font-bold shadow-sm"
                            style="background-color: #fff5f7; border-color: #5D0A28; color: #5D0A28;"
                            readonly>

                        {{-- Mostrar las fechas del periodo si existen --}}
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

                        {{-- Campo oculto con el ID del periodo para enviar al controlador --}}
                        @if(isset($periodoActivo->id))
                            <input type="hidden" name="periodo_id" value="{{ $periodoActivo->id }}">
                        @endif
                    </div>
                </div>

                {{-- ===============================================
                    CHECKBOX DE EXCEPCION
                    Solo se muestra si:
                    - Existe una evaluacion anterior (no es Evaluacion 1), O
                    - Estamos en modo excepcion forzado

                    En modo excepcion forzado:
                    - El checkbox esta marcado y deshabilitado (no se puede desmarcar)
                    - Se agrega un input hidden para enviar el valor "1" al servidor
                      (los checkbox deshabilitados no envian datos en HTML)

                    En modo normal:
                    - El checkbox esta disponible y el estudiante puede marcarlo
                    - Al marcarlo, se muestra el dropdown con la evaluacion anterior
                      y la evidencia se vuelve obligatoria (via JavaScript)
                =============================================== --}}
                @if($evaluacionAnterior || $modoExcepcion)
                <div class="sm:col-span-2 bg-amber-50 p-4 rounded-lg border border-amber-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="es_excepcion" id="check_excepcion" value="1"
                            class="w-5 h-5 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                            {{ $modoExcepcion ? 'checked disabled' : '' }}>
                        {{-- Input hidden necesario porque los checkbox deshabilitados no se envian --}}
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

                    {{-- ===============================================
                        DROPDOWN DE EVALUACION ANTERIOR
                        Muestra la evaluacion para la cual se solicita
                        la excepcion. Solo tiene una opcion: la evaluacion
                        inmediatamente anterior a la activa.
                        Ejemplo: si la activa es "Evaluacion 3",
                        el dropdown muestra "Evaluación 2".

                        Esta oculto por defecto en modo normal y se
                        muestra cuando el checkbox se marca (via JS).
                        En modo excepcion forzado, se muestra siempre.
                    =============================================== --}}
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

                {{-- ===============================================
                    SELECCION DE MATERIA
                    Lista desplegable con las materias inscritas del
                    estudiante. Cada opcion tiene atributos data-*
                    con el docente y la seccion, que se usan en
                    JavaScript para autocompletar esos campos.
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
                    SECCION Y DOCENTE
                    Se autocompletar al seleccionar una materia.
                    Son de solo lectura para el estudiante.
                    Los valores reales se envian en inputs hidden.
                =============================================== --}}
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

                {{-- ===============================================
                    NOTA A PROPONER
                    El estudiante ingresa la nota que cree correcta.
                    Tiene validacion en tiempo real con JavaScript:
                    si el valor es menor a 0 o mayor a 10, se muestra
                    un error y se deshabilita el boton de enviar.
                =============================================== --}}
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

                {{-- ===============================================
                    MOTIVO DEL RECLAMO
                    Campo de texto donde el estudiante explica
                    por que solicita la correccion. Es obligatorio
                    y debe tener al menos 10 caracteres.
                =============================================== --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Motivo del Reclamo</label>
                    <textarea name="motivo" rows="4"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        placeholder="Explique detalladamente por qué solicita la corrección..."
                        required></textarea>
                </div>

                {{-- ===============================================
                    EVIDENCIA (ARCHIVO)
                    Campo para subir archivos JPG, PNG o PDF (max 5MB).

                    Comportamiento segun el modo:
                    - MODO NORMAL sin checkbox: Opcional, borde gris
                    - MODO NORMAL con checkbox marcado: Obligatorio,
                      borde rojo, texto rojo (cambia via JavaScript)
                    - MODO EXCEPCION FORZADO: Obligatorio desde el inicio,
                      borde rojo, atributo required en el HTML

                    El label tiene dos textos que se alternan:
                    - "Obligatorio para excepciones" (visible si excepcion)
                    - "Opcional" (visible si solicitud normal)
                =============================================== --}}
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
                    <input type="file" name="evidencia" id="input_evidencia" accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full p-2 border-2 rounded-lg bg-white text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold transition"
                        style="border-color: {{ $modoExcepcion ? '#fca5a5' : '#e5e7eb' }};"
                        {{ $modoExcepcion ? 'required' : '' }}>
                    <p class="text-xs mt-1" style="color: {{ $modoExcepcion ? '#ef4444' : '#6b7280' }};" id="texto_formato_evidencia">
                        Formatos: JPG, PNG, PDF — Maximo 5MB
                    </p>
                </div>

            </div>

            {{-- ===============================================
                BOTONES DE ENVIO
                El texto del boton cambia segun el modo:
                - Normal: "Enviar Solicitud al Docente"
                - Excepcion: "Enviar Solicitud de Excepción"
                El boton se deshabilita si la nota es invalida.
            =============================================== --}}
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

    {{-- ===============================================
        JAVASCRIPT
        Tres funcionalidades principales:
        1) Autocompletar seccion y docente al seleccionar materia
        2) Validar nota en tiempo real (0 a 10)
        3) Logica del checkbox de excepcion:
           - Mostrar/ocultar dropdown de evaluacion anterior
           - Cambiar evidencia a obligatoria/opcional
           - Cambiar texto y colores del label de evidencia
           - Cambiar texto del boton de enviar
    =============================================== --}}
    <script>
        // 1) AUTOCOMPLETAR SECCION Y DOCENTE
        // Al seleccionar una materia, lee los atributos data-* de la opcion
        // y los coloca en los campos de seccion y docente
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

        // 2) VALIDACION DE NOTA EN TIEMPO REAL
        // Si la nota es mayor a 10, menor a 0 o no es un numero,
        // muestra el error y deshabilita el boton de enviar.
        // Si es valida, oculta el error y habilita el boton.
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
        // Solo se agrega el listener si el checkbox existe y NO esta deshabilitado
        // (en modo excepcion forzado esta deshabilitado, asi que no necesita listener)
        var checkExcepcion = document.getElementById('check_excepcion');
        if (checkExcepcion && !checkExcepcion.disabled) {
            checkExcepcion.addEventListener('change', function () {
                var contenedorEval = document.getElementById('contenedor_eval_excepcion');
                var inputEvidencia = document.getElementById('input_evidencia');
                var textoObligatorio = document.getElementById('texto_evidencia_obligatoria');
                var textoOpcional = document.getElementById('texto_evidencia_opcional');
                var labelEvidencia = document.getElementById('label_evidencia');
                var textoFormato = document.getElementById('texto_formato_evidencia');
                var textoBtn = document.getElementById('texto_btn_enviar');

                if (this.checked) {
                    // EXCEPCION ACTIVADA: mostrar dropdown, hacer evidencia obligatoria
                    contenedorEval.classList.remove('hidden');
                    inputEvidencia.required = true;
                    inputEvidencia.style.borderColor = '#fca5a5';
                    textoObligatorio.classList.remove('hidden');
                    textoOpcional.classList.add('hidden');
                    labelEvidencia.style.color = '#dc2626';
                    textoFormato.style.color = '#ef4444';
                    textoBtn.textContent = 'Enviar Solicitud de Excepción';
                } else {
                    // EXCEPCION DESACTIVADA: ocultar dropdown, evidencia vuelve a ser opcional
                    contenedorEval.classList.add('hidden');
                    inputEvidencia.required = false;
                    inputEvidencia.style.borderColor = '#e5e7eb';
                    textoObligatorio.classList.add('hidden');
                    textoOpcional.classList.remove('hidden');
                    labelEvidencia.style.color = '#374151';
                    textoFormato.style.color = '#6b7280';
                    textoBtn.textContent = 'Enviar Solicitud al Docente';
                }
            });
        }
    </script>
</body>
</html>
