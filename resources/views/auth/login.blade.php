<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Notas UTEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-color: #5D0A28;" class="h-screen flex items-center justify-center">

    <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-md border-t-8" style="border-color: #5D0A28;">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-800 uppercase tracking-tight">
                Universidad <span style="color: #5D0A28;">Tecnológica</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 italic">Sistema de Corrección de Notas</p>
        </div>

        <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase">Correo Institucional</label>
                <input type="email" name="correo"
                    placeholder="ejemplo@utec.edu.sv"
                    style="--tw-ring-color: #5D0A28;"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-[#5D0A28] transition duration-200"
                    required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1 uppercase">Contraseña</label>
                <input type="password" name="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-[#5D0A28] transition duration-200"
                    required>
            </div>

            <button type="submit"
                style="background-color: #5D0A28;"
                onmouseover="this.style.backgroundColor='#4A0820'"
                onmouseout="this.style.backgroundColor='#5D0A28'"
                class="w-full text-white py-3 rounded-lg font-bold uppercase tracking-widest transform hover:scale-[1.02] transition-all shadow-lg active:scale-95">
                Iniciar Sesión
            </button>
        </form>
        {{-- ===============================================
            ALERTAS DE ERROR DE VALIDACIÓN
            Si el controlador redirige con errores de validación (con ->withErrors()),
            se muestran aquí. Solo se muestra el primer error para no saturar la interfaz.
        =============================================== --}}

        @if ($errors->any())
            <div class="mt-6 bg-red-50 border-l-4 border-red-500 p-3">
                <p class="text-red-700 text-sm font-medium">
                    {{ $errors->first() }}
                </p>
            </div>
        @endif

        <div class="mt-8 text-center border-t pt-6">
            <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">
                Facultad de Informática y Ciencias Aplicadas
            </p>
        </div>
    </div>

</body>
</html>