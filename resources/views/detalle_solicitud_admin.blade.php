<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicar Corrección — UTEC Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen pb-12">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC <span class="hidden sm:inline">— Panel Administrador</span></h1>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">Admin</span>
                <span class="font-medium text-sm hidden sm:inline">{{ Auth::user()->nombre }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-300 transition" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto max-w-4xl mt-8 px-4">

        {{-- Botón volver --}}
        <a href="/admin/dashboard" style="color: #5D0A28;"
            class="inline-flex items-center font-bold text-sm hover:underline mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
        </a>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- DATOS DE LA SOLICITUD                          --}}
        {{-- ══════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-2" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-file-alt"></i> Datos de la Solicitud
                </h2>
                @if($solicitud->es_excepcion)
                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-300 px-3 py-1.5 rounded-full text-xs font-bold uppercase">
                        <i class="fas fa-exclamation-circle"></i> Excepción
                    </span>
                @endif
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Estudiante --}}
                <div class="sm:col-span-2 bg-gray-50 border rounded-lg p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Estudiante</p>
                    <p class="text-gray-800 font-bold text-base">{{ $solicitud->estudiante_nombre }}</p>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">Carnet: {{ $solicitud->estudiante_carnet }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $solicitud->carrera_nombre }} — {{ $solicitud->facultad_nombre }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Materia</p>
                    <p class="text-gray-800 font-bold">{{ $solicitud->materia_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Docente</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->docente_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Evaluación</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->evaluacion }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ciclo</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->ciclo }}</p>
                </div>

                {{-- Nota reclamada destacada --}}
                <div class="sm:col-span-2 bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-1">
                        Nota que el estudiante reclama tener
                    </p>
                    <p class="text-4xl font-extrabold text-red-500">{{ $solicitud->nota_actual }}</p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Justificación del Estudiante</p>
                    <p class="text-gray-700 bg-gray-50 border rounded-lg p-3 text-sm leading-relaxed">
                        {{ $solicitud->motivo }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- DECISIÓN DEL DOCENTE                           --}}
        {{-- ══════════════════════════════════════════════ --}}
        @if($decisionDocente)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-2">
                <i class="fas fa-chalkboard-teacher mr-1"></i> Revisión del Docente
            </p>
            <p class="text-sm font-bold text-blue-800">{{ $decisionDocente->accion }}</p>
            <p class="text-xs text-blue-600 mt-0.5">
                {{ \Carbon\Carbon::parse($decisionDocente->fecha)->format('d/m/Y H:i') }}
                — {{ $decisionDocente->actor_nombre }}
            </p>
            @if($decisionDocente->comentario)
                <div class="mt-2 bg-white border-l-4 border-blue-400 rounded p-3">
                    <p class="text-xs font-bold text-blue-500 uppercase mb-1">
                        <i class="fas fa-lightbulb mr-1"></i> Comentario del docente
                    </p>
                    <p class="text-sm text-gray-700 italic leading-relaxed">
                        "{{ $decisionDocente->comentario }}"
                    </p>
                </div>
            @endif

            {{-- NOTA SUGERIDA PRIVADA — solo la ve el admin --}}
            @if($decisionDocente->nota_sugerida_admin)
                <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-500 rounded p-3">
                    <p class="text-xs font-bold text-yellow-600 uppercase mb-1">
                        <i class="fas fa-lock mr-1"></i> Nota sugerida por el docente (solo visible para Admin)
                    </p>
                    <p class="text-sm text-gray-800 font-semibold leading-relaxed">
                        {{ $decisionDocente->nota_sugerida_admin }}
                    </p>
                </div>
            @endif
        </div>
        @endif

        {{-- ══════════════════════════════════════════════ --}}
        {{-- DECISIÓN DEL COORDINADOR                       --}}
        {{-- ══════════════════════════════════════════════ --}}
        @if($decisionCoordinador)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-2">
                <i class="fas fa-user-tie mr-1"></i> Revisión del Coordinador
            </p>
            <p class="text-sm font-bold text-purple-800">{{ $decisionCoordinador->accion }}</p>
            <p class="text-xs text-purple-600 mt-0.5">
                {{ \Carbon\Carbon::parse($decisionCoordinador->fecha)->format('d/m/Y H:i') }}
                — {{ $decisionCoordinador->actor_nombre }}
            </p>
            @if($decisionCoordinador->comentario)
                <p class="text-sm text-gray-700 mt-2 italic bg-white border rounded p-2 leading-relaxed">
                    "{{ $decisionCoordinador->comentario }}"
                </p>
            @endif
        </div>
        @endif

        {{-- EVIDENCIAS POR ROL --}}
        @foreach([
            ['col' => $evidenciasEstudiante, 'titulo' => 'Evidencia del Estudiante', 'color' => 'blue'],
            ['col' => $evidenciasDocente, 'titulo' => 'Evidencia del Docente', 'color' => 'gray'],
            ['col' => $evidenciasCoordinador, 'titulo' => 'Evidencia del Coordinador', 'color' => 'purple'],
            ['col' => $evidenciasAdmin, 'titulo' => 'Tu Evidencia Adjunta', 'color' => 'green'],
        ] as $grupo)
            @if($grupo['col']->count())
            <div class="bg-{{ $grupo['color'] }}-50 border border-{{ $grupo['color'] }}-200 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-{{ $grupo['color'] }}-500 uppercase tracking-wider mb-2">
                    <i class="fas fa-paperclip mr-1"></i> {{ $grupo['titulo'] }} ({{ $grupo['col']->count() }} archivo{{ $grupo['col']->count() > 1 ? 's' : '' }})
                </p>
                @foreach($grupo['col'] as $ev)
                <div class="flex items-center gap-3 mt-2 {{ !$loop->first ? 'border-t border-' . $grupo['color'] . '-100 pt-2' : '' }}">
                    <a href="{{ route('evidencia.ver', $ev->id) }}" target="_blank" style="color: #5D0A28;" class="text-sm font-bold hover:underline inline-flex items-center gap-1.5"><i class="fas fa-eye"></i> Ver</a>
                    <a href="{{ route('evidencia.descargar', $ev->id) }}" style="color: #5D0A28;" class="text-sm font-bold hover:underline inline-flex items-center gap-1.5"><i class="fas fa-download"></i> Descargar</a>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($ev->fecha)->format('d/m/Y H:i') }}</span>
                </div>
                @endforeach
            </div>
            @endif
        @endforeach

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- FORMULARIO DE CORRECCIÓN                                       --}}
        {{-- Admin ingresa la nota correcta y finaliza — SIN posibilidad    --}}
        {{-- de editar después. Acción irreversible.                        --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-flag-checkered"></i> Aplicar Corrección de Nota
                </h2>
            </div>

            {{-- Advertencia de acción irreversible --}}
            <div class="mx-6 mt-5 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <p class="text-yellow-800 text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    Acción irreversible — Una vez guardada la corrección, nadie podrá modificarla.
                </p>
            </div>

            {{-- Errores de validación --}}
            @if($solicitud->estado === 'finalizado' && $historialNota)
            <div class="p-6">
                <div class="bg-green-50 border-2 border-green-400 rounded-xl p-6">
                    <h3 class="text-green-800 font-extrabold text-lg uppercase tracking-wider flex items-center gap-2 mb-4">
                        <i class="fas fa-trophy text-green-600"></i> Resultado Final
                    </h3>
                    <div class="flex items-center justify-center gap-8 flex-wrap">
                        <div class="text-center">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nota Propuesta</p>
                            <p class="text-4xl font-extrabold text-red-500">{{ $historialNota->nota_anterior }}</p>
                        </div>
                        <div class="text-center"><i class="fas fa-arrow-right text-3xl text-gray-400"></i></div>
                        <div class="text-center">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nota Nueva</p>
                            <p class="text-4xl font-extrabold text-green-600">{{ $historialNota->nota_nueva }}</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="/admin/solicitud/{{ $solicitud->id }}/pdf"
                            style="background-color: #5D0A28;"
                            onmouseover="this.style.backgroundColor='#4A0820'"
                            onmouseout="this.style.backgroundColor='#5D0A28'"
                            class="text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wide transition inline-flex items-center gap-2 shadow">
                            <i class="fas fa-file-pdf"></i> Descargar Constancia PDF
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if($solicitud->estado === 'pendiente_admin')
            @if($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                    @foreach($errors->all() as $error)
                        <p class="text-red-700 text-sm font-medium"><i class="fas fa-exclamation-triangle text-yellow-500"></i> {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="/admin/solicitud/{{ $solicitud->id }}/finalizar"
                method="POST"
                enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf

                {{-- Nota nueva --}}
                <div id="contenedor_nota">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                        Nota Correcta a Registrar
                        <span class="text-gray-400 font-normal normal-case ml-1">(0.0 — 10.0)</span>
                    </label>
                    <input type="number"
                        step="0.01"
                        min="0"
                        max="10"
                        name="nota_nueva"
                        id="input_nota_nueva"
                        value="{{ old('nota_nueva') }}"
                        class="w-full p-4 border-2 border-gray-200 rounded-lg outline-none text-2xl font-extrabold transition"
                        style="color: #5D0A28;"
                        placeholder="Ej: 8.50"
                        required>
                    <p id="error_nota" class="text-red-600 text-sm font-bold mt-2 hidden">
                        <i class="fas fa-exclamation-triangle text-yellow-500"></i> La nota debe estar entre 0.0 y 10.0.
                    </p>
                </div>

                {{-- Comentario opcional --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                        Comentario
                        <span class="text-gray-400 font-normal normal-case ml-1">(Opcional)</span>
                    </label>
                    <textarea name="comentario" rows="3"
                        class="w-full p-3 border-2 border-gray-200 rounded-lg outline-none focus:border-[#5D0A28] transition text-sm"
                        placeholder="Ej: Nota corregida según registro del docente para Evaluación 3...">{{ old('comentario') }}</textarea>
                </div>

                {{-- Evidencia (drag-and-drop, multiples archivos) --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                        Adjuntar Evidencia
                        <span class="text-gray-400 font-normal normal-case ml-1">(Opcional — JPG, PNG, PDF — máx. 5MB por archivo)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition cursor-pointer" id="zona_evidencia_admin">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Haz clic o arrastra archivos aquí</p>
                        <p class="text-xs text-gray-400 mt-1 italic">Puede agregar varios archivos</p>
                    </div>
                    <input type="file" id="input_selector_admin" accept=".jpg,.jpeg,.png,.pdf" multiple class="hidden">
                    <div id="contenedor_inputs_admin"></div>
                    <div id="lista_archivos_admin" class="hidden mt-3 space-y-1.5"></div>
                </div>

                {{-- Botón finalizar --}}
                <div class="flex gap-4 pt-2">
                    <button type="submit" id="btn_finalizar"
                        style="background-color: #5D0A28;"
                        onmouseover="this.style.backgroundColor='#4A0820'"
                        onmouseout="this.style.backgroundColor='#5D0A28'"
                        class="flex-1 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest text-sm">
                        <i class="fas fa-flag-checkered mr-2"></i> Finalizar y Aplicar Corrección
                    </button>
                    <a href="/admin/dashboard"
                        class="px-8 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition uppercase text-sm flex items-center">
                        Cancelar
                    </a>
                </div>

            </form>
            @endif
        </div>

    </div>

    <script>
        // Gestor de archivos del admin
        var archivosAdmin = [];
        var zonaAdmin = document.getElementById('zona_evidencia_admin');
        var inputSelectorAdmin = document.getElementById('input_selector_admin');
        var listaDivAdmin = document.getElementById('lista_archivos_admin');
        var contenedorInputsAdmin = document.getElementById('contenedor_inputs_admin');

        if (zonaAdmin) {
            zonaAdmin.addEventListener('click', function () { inputSelectorAdmin.click(); });
            inputSelectorAdmin.addEventListener('change', function () { agregarArchivosAdmin(this.files); this.value = ''; });

            zonaAdmin.addEventListener('dragover', function (e) { e.preventDefault(); this.style.borderColor = '#5D0A28'; this.style.backgroundColor = '#fff5f7'; });
            zonaAdmin.addEventListener('dragleave', function (e) { e.preventDefault(); this.style.backgroundColor = ''; this.style.borderColor = '#d1d5db'; });
            zonaAdmin.addEventListener('drop', function (e) { e.preventDefault(); this.style.backgroundColor = ''; this.style.borderColor = '#d1d5db'; if (e.dataTransfer.files.length > 0) agregarArchivosAdmin(e.dataTransfer.files); });
        }

        function agregarArchivosAdmin(fileList) {
            var extPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
            for (var i = 0; i < fileList.length; i++) {
                var archivo = fileList[i]; var ext = archivo.name.split('.').pop().toLowerCase();
                if (extPermitidas.indexOf(ext) === -1) { alert('"' + archivo.name + '" no es un formato permitido.'); continue; }
                if (archivo.size > 5 * 1024 * 1024) { alert('"' + archivo.name + '" supera los 5MB.'); continue; }
                var dup = false;
                for (var j = 0; j < archivosAdmin.length; j++) { if (archivosAdmin[j].name === archivo.name && archivosAdmin[j].size === archivo.size) { dup = true; break; } }
                if (!dup) archivosAdmin.push(archivo);
            }
            renderListaAdmin(); sincronizarInputsAdmin();
        }
        function eliminarArchivoAdmin(idx) { archivosAdmin.splice(idx, 1); renderListaAdmin(); sincronizarInputsAdmin(); }
        function eliminarTodosAdmin() { archivosAdmin = []; renderListaAdmin(); sincronizarInputsAdmin(); }
        function renderListaAdmin() {
            listaDivAdmin.innerHTML = '';
            if (archivosAdmin.length === 0) { listaDivAdmin.classList.add('hidden'); return; }
            listaDivAdmin.classList.remove('hidden');
            var enc = document.createElement('div'); enc.className = 'flex items-center justify-between';
            enc.innerHTML = '<p class="text-xs font-bold text-gray-600 uppercase tracking-wide flex items-center gap-1.5"><i class="fas fa-paperclip"></i> ' + archivosAdmin.length + ' archivo' + (archivosAdmin.length > 1 ? 's' : '') + ' adjunto' + (archivosAdmin.length > 1 ? 's' : '') + '</p><button type="button" onclick="eliminarTodosAdmin()" class="text-xs text-red-500 hover:text-red-700 font-bold"><i class="fas fa-trash-alt mr-1"></i>Quitar todos</button>';
            listaDivAdmin.appendChild(enc);
            for (var i = 0; i < archivosAdmin.length; i++) {
                var a = archivosAdmin[i]; var t = (a.size / 1024 / 1024).toFixed(2); var ex = a.name.split('.').pop().toUpperCase();
                var ic = ex === 'PDF' ? 'fa-file-pdf text-red-500' : 'fa-file-image text-blue-500';
                var f = document.createElement('div'); f.className = 'flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm';
                f.innerHTML = '<i class="fas ' + ic + ' text-lg"></i><span class="font-medium text-gray-700 truncate flex-1">' + a.name + '</span><span class="text-xs text-gray-400 font-mono whitespace-nowrap">' + t + ' MB</span><span class="text-xs font-bold px-1.5 py-0.5 rounded bg-gray-200 text-gray-600">' + ex + '</span><button type="button" onclick="eliminarArchivoAdmin(' + i + ')" class="text-red-400 hover:text-red-600 ml-1" title="Quitar"><i class="fas fa-times-circle"></i></button>';
                listaDivAdmin.appendChild(f);
            }
        }
        function sincronizarInputsAdmin() {
            contenedorInputsAdmin.innerHTML = '';
            for (var i = 0; i < archivosAdmin.length; i++) {
                var dt = new DataTransfer(); dt.items.add(archivosAdmin[i]);
                var inp = document.createElement('input'); inp.type = 'file'; inp.name = 'evidencias[]'; inp.files = dt.files; inp.style.display = 'none';
                contenedorInputsAdmin.appendChild(inp);
            }
        }

        var inputNota = document.getElementById('input_nota_nueva');
        if (inputNota) inputNota.addEventListener('input', function () {
            var valor      = parseFloat(this.value);
            var btnFin     = document.getElementById('btn_finalizar');
            var errorMsg   = document.getElementById('error_nota');

            if (isNaN(valor) || valor < 0 || valor > 10) {
                errorMsg.classList.remove('hidden');
                btnFin.disabled           = true;
                btnFin.style.opacity      = '0.5';
                btnFin.style.cursor       = 'not-allowed';
                this.style.borderColor    = '#ef4444';
            } else {
                errorMsg.classList.add('hidden');
                btnFin.disabled           = false;
                btnFin.style.opacity      = '1';
                btnFin.style.cursor       = 'pointer';
                this.style.borderColor    = '#5D0A28';
            }
        });
    </script>

</body>
</html>