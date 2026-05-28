<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud por Excepción - UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 pb-10">

    <nav style="background-color: #5D0A28;" class="p-4 text-white mb-8 shadow-xl">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-university text-2xl"></i>
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC — Solicitud por Excepción</h1>
            </div>
            <a href="/docente/dashboard" class="hover:text-red-300 transition text-sm font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Panel
            </a>
        </div>
    </nav>

    <div class="container mx-auto max-w-4xl px-4">

        @if(session('error'))
            <div id="alerta-error" class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

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

        <form action="/docente/excepciones/guardar" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-2xl rounded-xl p-8 border-t-8" style="border-color: #5D0A28;">
            @csrf

            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-amber-100 text-amber-700 border border-amber-300 px-3 py-1 rounded-full text-xs font-bold uppercase">
                        <i class="fas fa-exclamation-circle mr-1"></i> Excepción
                    </span>
                </div>
                <p class="text-sm text-gray-500 italic">
                    Utilice este formulario para crear una solicitud de corrección de nota fuera del periodo regular.
                    La solicitud será enviada al coordinador para su revisión.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-6">

                <div class="col-span-2 bg-gray-50 p-4 rounded-lg border">
                    <p class="text-sm text-gray-600 uppercase font-bold mb-1">Docente Solicitante</p>
                    <p class="text-lg font-bold" style="color: #5D0A28;">{{ Auth::user()->nombre }}</p>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Materia y Sección</label>
                    <select name="materia_seccion" id="select_materia"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        required>
                        <option value="">Seleccione la Materia...</option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->materia_id }}_{{ $materia->seccion }}"
                                data-materia-id="{{ $materia->materia_id }}"
                                data-seccion="{{ $materia->seccion }}">
                                {{ $materia->materia_nombre }} — Sección {{ $materia->seccion }} ({{ ucfirst($materia->modalidad) }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="materia_id" id="input_materia_id">
                    <input type="hidden" name="seccion" id="input_seccion">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Estudiante</label>
                    <select name="estudiante_id" id="select_estudiante"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        required disabled>
                        <option value="">Primero seleccione una materia...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Evaluación</label>
                    <input type="text"
                        value="{{ str_replace('Evaluacion', 'Evaluación', $evaluacionAnterior) }}"
                        class="w-full p-3 border-2 rounded-lg bg-gray-100 outline-none font-bold text-gray-700"
                        readonly>
                    <input type="hidden" name="evaluacion" value="{{ $evaluacionAnterior }}">
                    <p class="text-xs text-gray-500 mt-1 italic">
                        Solo se pueden crear excepciones para la evaluación anterior a la activa.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Nota a Proponer (0.0 a 10)</label>
                    <input type="number" step="0.1" min="0.0" max="10"
                        name="nota_actual" id="input_nota"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition"
                        placeholder="Ej: 7.5"
                        required>
                    <p id="error_nota" class="text-red-600 text-sm font-bold mt-2 hidden">
                        La nota no puede ser mayor a 10 ni menor a 0.
                    </p>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Motivo del Reclamo</label>
                    <textarea name="motivo" rows="3"
                        class="w-full p-3 border-2 rounded-lg outline-none focus:border-[#5D0A28] transition normal-case"
                        style="text-transform: none;"
                        placeholder="Explique el motivo de la corrección de nota del estudiante..."
                        required></textarea>
                </div>

                <div class="col-span-2 bg-amber-50 p-4 rounded-lg border border-amber-200">
                    <label class="block text-sm font-bold text-amber-800 uppercase mb-1">
                        <i class="fas fa-file-alt mr-1"></i> Justificación de la Excepción
                    </label>
                    <textarea name="justificacion" rows="3"
                        class="w-full p-3 border-2 border-amber-200 rounded-lg outline-none focus:border-amber-500 transition bg-white normal-case"
                        style="text-transform: none;"
                        placeholder="Justifique por qué esta solicitud se realiza fuera del periodo regular de corrección..."
                        required></textarea>
                    <p class="text-xs text-amber-600 mt-2 italic">
                        Esta justificación será visible para el coordinador y el administrador.
                    </p>

                    <div class="mt-4">
                        <label class="block text-sm font-bold text-amber-800 uppercase mb-1">
                            <i class="fas fa-paperclip mr-1"></i> Evidencia
                        </label>
                        <input type="file" name="evidencia" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full p-2 border-2 border-amber-200 rounded-lg bg-white text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 transition">
                        <p class="text-xs text-amber-500 mt-1">Formatos: JPG, PNG, PDF — Máximo 5MB</p>
                    </div>
                </div>

            </div>

            <div class="mt-8 flex space-x-4">
                <button type="submit" id="btn_enviar"
                    style="background-color: #5D0A28;"
                    onmouseover="this.style.backgroundColor='#4A0820'"
                    onmouseout="this.style.backgroundColor='#5D0A28'"
                    class="flex-1 text-white py-4 rounded-lg font-bold shadow-lg transition uppercase tracking-widest">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar Excepción al Coordinador
                </button>
                <a href="/docente/dashboard"
                    class="px-8 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition uppercase text-sm flex items-center">
                    Cancelar
                </a>
            </div>

        </form>
    </div>

    <script>
        var estudiantesPorMateria = @json($estudiantesPorMateria);

        document.getElementById('select_materia').addEventListener('change', function () {
            var opcion = this.options[this.selectedIndex];
            var materiaId = opcion.getAttribute('data-materia-id') || '';
            var seccion = opcion.getAttribute('data-seccion') || '';

            document.getElementById('input_materia_id').value = materiaId;
            document.getElementById('input_seccion').value = seccion;

            var selectEstudiante = document.getElementById('select_estudiante');
            selectEstudiante.innerHTML = '<option value="">Seleccione un estudiante...</option>';

            var key = materiaId + '_' + seccion;
            if (estudiantesPorMateria[key]) {
                estudiantesPorMateria[key].forEach(function (est) {
                    var opt = document.createElement('option');
                    opt.value = est.estudiante_id;
                    opt.textContent = est.estudiante_nombre + ' (' + est.estudiante_carnet + ')';
                    selectEstudiante.appendChild(opt);
                });
                selectEstudiante.disabled = false;
            } else {
                selectEstudiante.innerHTML = '<option value="">No hay estudiantes en esta sección</option>';
                selectEstudiante.disabled = true;
            }
        });

        document.querySelector('form').addEventListener('submit', function () {
            var select = document.getElementById('select_materia');
            var opcion = select.options[select.selectedIndex];
            if (opcion) {
                document.getElementById('input_materia_id').value = opcion.getAttribute('data-materia-id') || '';
                document.getElementById('input_seccion').value = opcion.getAttribute('data-seccion') || '';
            }
        });

        document.getElementById('input_nota').addEventListener('input', function () {
            var valor = parseFloat(this.value);
            var btnSubmit = document.getElementById('btn_enviar');
            var errorMsg = document.getElementById('error_nota');

            if (valor > 10 || valor < 0 || isNaN(valor)) {
                errorMsg.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnSubmit.style.opacity = '0.5';
                btnSubmit.style.cursor = 'not-allowed';
            } else {
                errorMsg.classList.add('hidden');
                btnSubmit.disabled = false;
                btnSubmit.style.opacity = '1';
                btnSubmit.style.cursor = 'pointer';
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            function desvanecerAlerta(id) {
                var el = document.getElementById(id);
                if (!el) return;
                setTimeout(function () {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-10px)';
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    setTimeout(function () { el.remove(); }, 500);
                }, 3000);
            }
            desvanecerAlerta('alerta-error');
        });
    </script>

</body>
</html>
