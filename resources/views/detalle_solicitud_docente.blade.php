<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Solicitud — UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen pb-12">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Portal Docente</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">Docente</span>
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

    <div class="container mx-auto max-w-4xl mt-8 px-4">

        {{-- Botón volver --}}
        <a href="/docente/dashboard"
            style="color: #5D0A28;"
            class="inline-flex items-center font-bold text-sm hover:underline mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Volver a mi Bandeja
        </a>

        {{-- ===================================================
            DETALLE DE LA SOLICITUD
            Se muestra toda la información relevante de la solicitud.
            En la parte superior se muestra un badge con el estado actual.
            - Pendiente Docente: Naranja
            - Rechazado Docente: Rojo
            - Pendiente Coordinador: Amarillo
            - Rechazado Coordinador: Rojo
            - Pendiente Admin: Azul
            - Finalizado: Verde
    =================================================== --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 flex items-center justify-between" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-file-alt"></i> Datos de la Solicitud
                </h2>
                {{-- Badge de estado actual --}}
                @php
                    $clases = [
                        'pendiente_docente'     => 'bg-orange-100 text-orange-700 border-orange-300',
                        'rechazado_docente'     => 'bg-red-100    text-red-700    border-red-300',
                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-300',
                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-300',
                        'finalizado'            => 'bg-green-100  text-green-700  border-green-300',
                    ];
                    $etiquetas = [
                        'pendiente_docente'     => 'Pendiente de tu revisión',
                        'rechazado_docente'     => 'Rechazada por ti',
                        'pendiente_coordinador' => 'Aprobada → en Coordinador',
                        'rechazado_coordinador' => 'Rechazada por Coordinador',
                        'pendiente_admin'       => 'En revisión Admin',
                        'finalizado'            => 'Finalizada',
                    ];
                    $estadoKey = $solicitud->estado;
                    $estilo    = $clases[$estadoKey]    ?? 'bg-gray-100 text-gray-600 border-gray-300';
                    $etiqueta  = $etiquetas[$estadoKey] ?? $estadoKey;
                @endphp
                <div class="flex items-center gap-2">
                    @if($solicitud->es_excepcion)
                        <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-300 px-3 py-1.5 rounded-full text-xs font-bold uppercase">
                            <i class="fas fa-exclamation-circle"></i> Excepción
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $estilo }}">
                        {{ $etiqueta }}
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Datos del estudiante --}}
                <div class="sm:col-span-2 bg-gray-50 border rounded-lg p-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Estudiante Solicitante</p>
                    <p class="text-gray-800 font-bold text-base">{{ $solicitud->estudiante_nombre }}</p>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">Carnet: {{ $solicitud->estudiante_carnet }}</p>
                </div>

                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Materia</p>
                    <p class="text-gray-800 font-bold">{{ $solicitud->materia_nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sección</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->seccion }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Evaluación</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->evaluacion }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ciclo</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->ciclo }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nota que el estudiante reclama</p>
                    <p class="text-3xl font-extrabold" style="color: #5D0A28;">{{ $solicitud->nota_actual }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Solicitud</p>
                    <p class="text-gray-600 text-sm font-medium">
                        {{ \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Justificación del Estudiante</p>
                    <p class="text-gray-700 bg-gray-50 border rounded-lg p-3 text-sm leading-relaxed">
                        {{ $solicitud->motivo }}
                    </p>
                </div>
            </div>
        </div>

        {{-- EVIDENCIA DEL ESTUDIANTE --}}
        @if($evidenciaEstudiante)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-2">
                <i class="fas fa-paperclip mr-1"></i> Evidencia adjunta por el Estudiante
            </p>
            <div class="flex items-center gap-3 mt-1">
                <a href="{{ route('evidencia.ver', $evidenciaEstudiante->id) }}" target="_blank"
                    style="color: #5D0A28;"
                    class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('evidencia.descargar', $evidenciaEstudiante->id) }}"
                    style="color: #5D0A28;"
                    class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">
                Subida el {{ \Carbon\Carbon::parse($evidenciaEstudiante->fecha)->format('d/m/Y H:i') }}
            </p>
        </div>
        @endif

        {{-- ===================================================
            DECISIÓN PREVIA DEL DOCENTE (si existe)
            Si el docente ya tomó una decisión antes, se muestra aquí para referencia.
            - Acción tomada (Aprobado/Rechazado)
            - Comentario o justificación
            - Fecha de la decisión
    =================================================== --}}
        @if($decisionDocente)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-2">
                <i class="fas fa-history mr-1"></i> Tu revisión anterior
            </p>
            <p class="text-sm font-bold text-blue-800">{{ $decisionDocente->accion }}</p>
            <p class="text-xs text-blue-600 mt-0.5">
                {{ \Carbon\Carbon::parse($decisionDocente->fecha)->format('d/m/Y H:i') }}
            </p>
            @if($decisionDocente->comentario)
                <p class="text-sm text-gray-700 mt-2 italic bg-white border rounded p-2">
                    "{{ $decisionDocente->comentario }}"
                </p>
            @endif
        </div>
        @endif

        {{-- ===================================================
            EVIDENCIA ADJUNTA
            Si el docente subió un archivo de evidencia en su revisión, se muestra aquí con un enlace para descargarlo o visualizarlo.
            - Solo se muestra si existe una evidencia asociada a la decisión del docente.
            - Se muestra el nombre del archivo y la fecha de subida.
    =================================================== --}}
        @if($evidencia)
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                <i class="fas fa-paperclip mr-1"></i> Evidencia adjunta
            </p>
            <div class="flex items-center gap-3 mt-1">
                <a href="{{ route('evidencia.ver', $evidencia->id) }}" target="_blank"
                    style="color: #5D0A28;"
                    class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('evidencia.descargar', $evidencia->id) }}"
                    style="color: #5D0A28;"
                    class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">
                Subida el {{ \Carbon\Carbon::parse($evidencia->fecha)->format('d/m/Y H:i') }}
            </p>
        </div>
        @endif

        {{-- ===================================================
            FORMULARIO DE DECISIÓN DEL DOCENTE
            Solo se muestra si el coordinador aún no ha actuado y el estado es pendiente_docente, pendiente_coordinador o rechazado_docente.
            Permite al docente registrar o editar su decisión (aprobar/rechazar), agregar un comentario y adjuntar evidencia.
            - Si el coordinador ya actuó, se muestra un mensaje indicando que la edición está bloqueada.
            - Si el estado no es ninguno de los anteriores, se muestra un mensaje indicando que la solicitud ya fue procesada.
    =================================================== --}}
        @if(!$coordinadorYaActuo && in_array($solicitud->estado, ['pendiente_docente', 'pendiente_coordinador', 'rechazado_docente']))
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-gavel"></i>
                    {{ $decisionDocente ? 'Editar mi Decisión' : 'Registrar Decisión' }}
                </h2>
            </div>

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-500 p-3 rounded">
                    @foreach($errors->all() as $error)
                        <p class="text-red-700 text-sm font-medium">⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="/docente/solicitud/{{ $solicitud->id }}/decision"
                method="POST"
                enctype="multipart/form-data"
                class="p-6 space-y-6">
                @csrf

                {{-- Botones de decisión --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-3">Tu Decisión</label>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="decision" value="aprobado" id="radio_aprobar"
                                class="sr-only peer" {{ old('decision') === 'aprobado' ? 'checked' : '' }}>
                            <div class="peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600
                                        border-2 border-gray-300 rounded-xl p-4 text-center transition-all
                                        hover:border-green-400 text-gray-600 font-bold uppercase text-sm">
                                <i class="fas fa-check-circle text-2xl mb-1 block"></i>
                                Aprobar Solicitud
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="decision" value="rechazado" id="radio_rechazar"
                                class="sr-only peer" {{ old('decision') === 'rechazado' ? 'checked' : '' }}>
                            <div class="peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600
                                        border-2 border-gray-300 rounded-xl p-4 text-center transition-all
                                        hover:border-red-400 text-gray-600 font-bold uppercase text-sm">
                                <i class="fas fa-times-circle text-2xl mb-1 block"></i>
                                Rechazar Solicitud
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Comentario --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                        Comentario
                        <span id="label_obligatorio" class="text-red-500 ml-1 hidden">(Obligatorio al rechazar)</span>
                        <span class="text-gray-400 font-normal normal-case ml-1">(Opcional al aprobar)</span>
                    </label>
                    <textarea name="comentario" id="campo_comentario" rows="4"
                        class="w-full p-3 border-2 border-gray-200 rounded-lg outline-none focus:border-[#5D0A28] transition text-sm"
                        placeholder="Escribe tu comentario o justificación aquí...">{{ old('comentario') }}</textarea>
                </div>
                {{-- Campo privado para el admin: solo visible si aprueba --}}
                <div id="campo_nota_admin" style="display:none;">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                        Nota sugerida para el Administrador
                        <span class="text-red-500 ml-1">(Obligatorio al aprobar)</span>  {{-- cambias el span --}}
                        </label>
                        <textarea name="nota_sugerida_admin" rows="2"
                            class="w-full p-3 border-2 border-gray-200 rounded-lg outline-none focus:border-[#5D0A28] transition text-sm"
                            placeholder="Ej: La nota correcta es 8.5,">{{ old('nota_sugerida_admin') }}</textarea>
                    </div>

                {{-- Evidencia --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">
                        Adjuntar Evidencia
                        <span class="text-gray-400 font-normal normal-case ml-1">(Opcional — JPG, PNG, PDF — máx. 5MB)</span>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-[#5D0A28] transition cursor-pointer"
                        onclick="document.getElementById('input_archivo').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Haz clic para seleccionar un archivo</p>
                        <p id="nombre_archivo" class="text-xs text-gray-400 mt-1 italic">Ningún archivo seleccionado</p>
                    </div>
                    <input type="file" name="evidencia" id="input_archivo"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="hidden">
                </div>

                {{-- Botón enviar --}}
                <div class="flex gap-4 pt-2">
                    <button type="submit" id="btn_enviar"
                        style="background-color: #5D0A28;"
                        onmouseover="this.style.backgroundColor='#4A0820'"
                        onmouseout="this.style.backgroundColor='#5D0A28'"
                        class="flex-1 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest text-sm">
                        <i class="fas fa-paper-plane mr-2"></i> Guardar Decisión
                    </button>
                    <a href="/docente/dashboard"
                        class="px-8 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition uppercase text-sm flex items-center">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- BLOQUEO: El coordinador ya actuó, no puede editar             --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @elseif($coordinadorYaActuo)
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 text-center">
            <i class="fas fa-lock text-4xl text-yellow-500 mb-3"></i>
            <p class="font-bold text-yellow-800 text-lg">Edición bloqueada</p>
            <p class="text-yellow-700 text-sm mt-1">
                El coordinador de facultad ya evaluó esta solicitud. Ya no puedes modificar tu decisión.
            </p>
        </div>
        @else
        <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-6 text-center">
            <i class="fas fa-check-double text-4xl text-gray-400 mb-3"></i>
            <p class="font-bold text-gray-600 text-lg">Esta solicitud ya fue procesada</p>
            <p class="text-gray-500 text-sm mt-1">El flujo continúa en las siguientes etapas.</p>
        </div>
        @endif

    </div>
    {{-- ===================================================
        SCRIPTS
        Aquí se pueden agregar scripts específicos para esta vista, como mostrar el nombre del archivo seleccionado
        o mostrar una advertencia de comentario obligatorio al seleccionar "Rechazar".
    =================================================== --}}
    <script>
        // Mostrar nombre del archivo seleccionado
        document.getElementById('input_archivo').addEventListener('change', function () {
            var nombre = this.files[0] ? this.files[0].name : 'Ningún archivo seleccionado';
            document.getElementById('nombre_archivo').textContent = nombre;
        });

        // Mostrar advertencia de comentario obligatorio al seleccionar "Rechazar"
        var radios = document.querySelectorAll('input[name="decision"]');
        var labelObligatorio = document.getElementById('label_obligatorio');

        radios.forEach(function(radio) {
            radio.addEventListener('change', function () {
                if (this.value === 'rechazado') {
                    labelObligatorio.classList.remove('hidden');
                    document.getElementById('campo_comentario').placeholder =
                        'Obligatorio: explica por qué rechazas esta solicitud...';
                } else {
                    labelObligatorio.classList.add('hidden');
                    document.getElementById('campo_comentario').placeholder =
                        'Escribe tu comentario o justificación aquí...';
                }
            });
        });
            // Mostrar/ocultar campo nota_admin según decisión
        document.querySelectorAll('input[name="decision"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var campo = document.getElementById('campo_nota_admin');
                campo.style.display = this.value === 'aprobado' ? 'block' : 'none';
        });
    });
    // Si la página se recarga y "Aprobar" está seleccionado, mostrar el campo nota_admin
    document.addEventListener('DOMContentLoaded', function() {
        var radioAprobado = document.getElementById('radio_aprobar');
        if (radioAprobado && radioAprobado.checked) {
            document.getElementById('campo_nota_admin').style.display = 'block';
        }
    });
    </script>

</body>
</html>