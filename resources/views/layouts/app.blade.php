{{-- =====================================================
    LAYOUT PRINCIPAL DE LA APLICACION
    Todas las vistas extienden este layout usando:
      @extends('layouts.app')
      @section('title', 'Mi Titulo')
      @section('subtitle', 'Mi Subtitulo')
      @section('rol', 'estudiante')
      @section('content') ... @endsection

    Elementos compartidos:
      - Head con Tailwind CSS y Font Awesome
      - Navbar con logo UTEC, rol del usuario y logout
      - Alertas flash de error y exito
      - Script para desvanecer alertas automaticamente
===================================================== --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UTEC') - Sistema de Corrección de Notas</title>
    <link rel="icon" href="{{ asset('images/utec-logo.jpeg') }}" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen @yield('body-class')">

    {{-- NAVBAR --}}
    <nav style="background-color: #5D0A28;" class="p-4 text-white shadow-xl @yield('nav-class')">
        <div class="container mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('images/utec-logo.jpeg') }}" alt="UTEC" class="h-10 rounded">
                <h1 class="font-bold text-xl uppercase tracking-wider">UTEC <span class="hidden sm:inline">— @yield('subtitle', 'Portal')</span></h1>
            </div>
            <div class="flex items-center space-x-3">
                @hasSection('rol')
                <span class="bg-white font-bold uppercase px-3 py-1 rounded-full text-xs" style="color: #5D0A28;">
                    @yield('rol')
                </span>
                @endif
                @auth
                <span class="font-medium text-sm hidden sm:inline">{{ Auth::user()->nombre }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-red-300 transition" title="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt text-lg"></i>
                    </button>
                </form>
                @endauth
                @yield('nav-right')
            </div>
        </div>
    </nav>

    {{-- ALERTAS FLASH --}}
    <div class="container mx-auto mt-6 px-4">
        @if(session('error'))
            <div id="alerta-error" class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div id="alerta-exito" class="bg-green-50 border-l-4 border-green-600 text-green-700 p-4 mb-4 rounded-r-lg shadow-md font-medium text-sm flex items-center transition-all duration-500">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
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
    </div>

    {{-- CONTENIDO PRINCIPAL --}}
    @yield('content')

    {{-- SCRIPTS COMPARTIDOS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function desvanecerAlerta(id) {
                var el = document.getElementById(id);
                if (!el) return;
                setTimeout(function () {
                    el.style.opacity    = '0';
                    el.style.transform  = 'translateY(-10px)';
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    setTimeout(function () { el.remove(); }, 500);
                }, 5000);
            }
            desvanecerAlerta('alerta-error');
            desvanecerAlerta('alerta-exito');
        });
    </script>

    {{-- SCRIPTS ADICIONALES DE CADA VISTA --}}
    @yield('scripts')

</body>
</html>
