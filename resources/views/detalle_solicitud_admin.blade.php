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

        {{-- Evidencia del docente --}}
        @if($evidenciaDocente)
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 mb-6">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                <i class="fas fa-paperclip mr-1"></i> Evidencia del Docente
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
        </div>
        @endif

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
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nota Anterior</p>
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