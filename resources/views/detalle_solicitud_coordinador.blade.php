<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Solicitud — Coordinador UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen pb-12">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl">
        <div class="container mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC <span class="hidden sm:inline">— Portal Coordinador</span></h1>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">Coordinador</span>
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
        <a href="/coordinador/dashboard" style="color: #5D0A28;"
            class="inline-flex items-center font-bold text-sm hover:underline mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Volver a mi Bandeja
        </a>

        {{-- ══════════════════════════════════════════════ --}}
        {{-- DATOS DE LA SOLICITUD                          --}}
        {{-- ══════════════════════════════════════════════ --}}
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-2" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-file-alt"></i> Datos de la Solicitud
                </h2>
                @php
                    $clases = [
                        'pendiente_coordinador' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                        'rechazado_coordinador' => 'bg-red-100    text-red-700    border-red-300',
                        'pendiente_admin'       => 'bg-blue-100   text-blue-700   border-blue-300',
                        'finalizado'            => 'bg-green-100  text-green-700  border-green-300',
                    ];
                    $etiquetas = [
                        'pendiente_coordinador' => 'Pendiente de tu revisión',
                        'rechazado_coordinador' => 'Rechazada por ti',
                        'pendiente_admin'       => 'Aprobada → en Admin',
                        'finalizado'            => 'Finalizada',
                    ];
                    $estadoKey = $solicitud->estado;
                    $estilo    = $clases[$estadoKey] ?? 'bg-gray-100 text-gray-600 border-gray-300';
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
                    <p class="text-xs text-gray-400 mt-0.5">Carrera: {{ $solicitud->carrera_nombre }}</p>
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
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nota Reclamada</p>
                    <p class="text-3xl font-extrabold" style="color: #5D0A28;">{{ $solicitud->nota_actual }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sección</p>
                    <p class="text-gray-800 font-semibold">{{ $solicitud->seccion }}</p>
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
        {{-- DECISIÓN DEL DOCENTE (para que el coordinador  --}}
        {{-- vea qué dijo el docente y su comentario)       --}}
        {{-- ══════════════════════════════════════════════ --}}
        @if($decisionDocente)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-2">
                <i class="fas fa-chalkboard-teacher mr-1"></i> Decisión del Docente
            </p>
            <p class="text-sm font-bold text-blue-800">{{ $decisionDocente->accion }}</p>
            <p class="text-xs text-blue-600 mt-0.5">
                {{ \Carbon\Carbon::parse($decisionDocente->fecha)->format('d/m/Y H:i') }}
                — {{ $decisionDocente->actor_nombre }}
            </p>
            @if($decisionDocente->comentario)
                <p class="text-sm text-gray-700 mt-2 italic bg-white border rounded p-2 leading-relaxed">
                    "{{ $decisionDocente->comentario }}"
                </p>
            @endif
        </div>
        @endif

        {{-- ══════════════════════════════════════════════ --}}
        {{-- EVIDENCIA DEL DOCENTE (si subió archivo)       --}}
        {{-- ══════════════════════════════════════════════ --}}
        @if($evidenciaDocente)
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                <i class="fas fa-paperclip mr-1"></i> Evidencia adjunta por el Docente
            </p>
            <div class="flex items-center gap-3 mt-1">
                <a href="{{ route('evidencia.ver', $evidenciaDocente->id) }}" target="_blank"
                    style="color: #5D0A28;"
                    class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('evidencia.descargar', $evidenciaDocente->id) }}"
                    style="color: #5D0A28;"
                    class="text-sm font-bold hover:underline inline-flex items-center gap-1.5">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">
                Subida el {{ \Carbon\Carbon::parse($evidenciaDocente->fecha)->format('d/m/Y H:i') }}
            </p>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════ --}}
        {{-- DECISIÓN PREVIA DEL COORDINADOR (si ya actuó) --}}
        {{-- ══════════════════════════════════════════════ --}}
        @if($decisionCoordinador)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-2">
                <i class="fas fa-history mr-1"></i> Tu revisión anterior
            </p>
            <p class="text-sm font-bold text-purple-800">{{ $decisionCoordinador->accion }}</p>
            <p class="text-xs text-purple-600 mt-0.5">
                {{ \Carbon\Carbon::parse($decisionCoordinador->fecha)->format('d/m/Y H:i') }}
            </p>
            @if($decisionCoordinador->comentario)
                <p class="text-sm text-gray-700 mt-2 italic bg-white border rounded p-2">
                    "{{ $decisionCoordinador->comentario }}"
                </p>
            @endif
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- FORMULARIO DE DECISIÓN                                         --}}
        {{-- Solo si el admin NO ha actuado aún                             --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        @if(!$adminYaActuo && in_array($solicitud->estado, ['pendiente_coordinador', 'pendiente_admin', 'rechazado_coordinador']))
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4" style="background-color: #5D0A28;">
                <h2 class="text-white font-bold text-lg uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-gavel"></i>
                    {{ $decisionCoordinador ? 'Editar mi Decisión' : 'Registrar Decisión' }}
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

            <form action="/coordinador/solicitud/{{ $solicitud->id }}/decision"
                method="POST"
                class="p-6 space-y-6">
                @csrf

                {{-- Botones de decisión --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-3">Tu Decisión</label>
                    <div class="flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="decision" value="aprobado"
                                class="sr-only peer" {{ old('decision') === 'aprobado' ? 'checked' : '' }}>
                            <div class="peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600
                                        border-2 border-gray-300 rounded-xl p-4 text-center transition-all
                                        hover:border-green-400 text-gray-600 font-bold uppercase text-sm">
                                <i class="fas fa-check-circle text-2xl mb-1 block"></i>
                                Aprobar Solicitud
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="decision" value="rechazado"
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
                        placeholder="Escribe tu comentario aquí...">{{ old('comentario') }}</textarea>
                </div>

                {{-- Botones --}}
                <div class="flex gap-4 pt-2">
                    <button type="submit"
                        style="background-color: #5D0A28;"
                        onmouseover="this.style.backgroundColor='#4A0820'"
                        onmouseout="this.style.backgroundColor='#5D0A28'"
                        class="flex-1 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest text-sm">
                        <i class="fas fa-paper-plane mr-2"></i> Guardar Decisión
                    </button>
                    <a href="/coordinador/dashboard"
                        class="px-8 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition uppercase text-sm flex items-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        {{-- Admin ya actuó — bloqueo --}}
        @elseif($adminYaActuo)
        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6 text-center">
            <i class="fas fa-lock text-4xl text-yellow-500 mb-3"></i>
            <p class="font-bold text-yellow-800 text-lg">Edición bloqueada</p>
            <p class="text-yellow-700 text-sm mt-1">
                El administrador ya procesó esta solicitud. Ya no puedes modificar tu decisión.
            </p>
        </div>
        @else
        <div class="bg-gray-50 border-2 border-gray-200 rounded-xl p-6 text-center">
            <i class="fas fa-check-double text-4xl text-gray-400 mb-3"></i>
            <p class="font-bold text-gray-600 text-lg">Esta solicitud ya fue procesada</p>
            <p class="text-gray-500 text-sm mt-1">El flujo continúa con el administrador.</p>
        </div>
        @endif

    </div>

    <script>
        // Mostrar aviso de comentario obligatorio al seleccionar Rechazar
        var radios = document.querySelectorAll('input[name="decision"]');
        var labelObligatorio = document.getElementById('label_obligatorio');

        if (radios.length && labelObligatorio) {
            radios.forEach(function(radio) {
                radio.addEventListener('change', function () {
                    if (this.value === 'rechazado') {
                        labelObligatorio.classList.remove('hidden');
                        document.getElementById('campo_comentario').placeholder =
                            'Obligatorio: explica por qué rechazas esta solicitud...';
                    } else {
                        labelObligatorio.classList.add('hidden');
                        document.getElementById('campo_comentario').placeholder =
                            'Escribe tu comentario aquí...';
                    }
                });
            });
        }
    </script>

</body>
</html>