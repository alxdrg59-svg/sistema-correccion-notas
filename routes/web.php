<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EvidenciaController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Rutas protegidas por autenticación y rol
// Dashboard del estudiante, muestra sus solicitudes
Route::get('/estudiante/dashboard', [SolicitudController::class, 'index'])
    ->middleware(['auth', 'rol:estudiante']);

// Ruta para mostrar el formulario de nueva solicitud
// Solo se muestra si el periodo de recepción está activo y el estudiante cumple con los requisitos
Route::get('/estudiante/nueva-solicitud', [SolicitudController::class, 'crearSolicitud'])
    ->middleware(['auth', 'rol:estudiante']);

// Ruta para guardar la solicitud (esta es la que se llama al enviar el formulario)
// Solo se puede acceder a esta ruta si el estudiante está autenticado y tiene el rol correcto
Route::post('/estudiante/guardar-solicitud', [SolicitudController::class, 'guardarSolicitud'])
    ->middleware(['auth', 'rol:estudiante']);

//  Ruta para ver el detalle de una solicitud específica
// Solo el estudiante dueño de la solicitud puede verla, y solo si está autenticado con el rol correcto
Route::get('/estudiante/solicitud/{id}', [SolicitudController::class, 'verDetalle'])
    ->middleware(['auth', 'rol:estudiante']);

Route::get('/estudiante/solicitud/{id}/pdf', [SolicitudController::class, 'exportarPdf'])
    ->middleware(['auth', 'rol:estudiante']);

// Ruta para cancelar una solicitud (solo si esta en pendiente_docente y dentro de las 3 horas)
Route::post('/estudiante/solicitud/{id}/cancelar', [SolicitudController::class, 'cancelarSolicitud'])
    ->middleware(['auth', 'rol:estudiante']);

Route::post('/estudiante/solicitud/{id}/agregar-evidencia', [SolicitudController::class, 'agregarEvidencia'])
    ->middleware(['auth', 'rol:estudiante']);

// Ruta para cancelar una solicitud (solo si está en estado pendiente_docente o pendiente_coordinador)
Route::get('/docente/dashboard', [DocenteController::class, 'index'])
    ->middleware(['auth', 'rol:docente']);

// Ruta para ver el detalle de una solicitud específica para el docente
// Solo el docente asignado a la solicitud puede verla, y solo si está autenticado con el rol correcto
Route::get('/docente/solicitud/{id}', [DocenteController::class, 'verDetalle'])
    ->middleware(['auth', 'rol:docente']);

// Ruta para procesar la decisión del docente (aprobar/rechazar)
// Solo el docente asignado a la solicitud puede acceder a esta ruta, y solo si está autenticado con el rol correcto
Route::post('/docente/solicitud/{id}/decision', [DocenteController::class, 'procesarDecision'])
    ->middleware(['auth', 'rol:docente']);

Route::get('/docente/solicitud/{id}/pdf', [DocenteController::class, 'exportarPdf'])
    ->middleware(['auth', 'rol:docente']);

// Rutas para coordinador
Route::get('/coordinador/dashboard', [CoordinadorController::class, 'index'])
    ->middleware(['auth', 'rol:coordinador']);

// Ruta para ver el detalle de una solicitud específica para el coordinador
Route::get('/coordinador/solicitud/{id}', [CoordinadorController::class, 'verDetalle'])
    ->middleware(['auth', 'rol:coordinador']);

    // Ruta para procesar la decisión del coordinador (aprobar/rechazar)
Route::post('/coordinador/solicitud/{id}/decision', [CoordinadorController::class, 'procesarDecision'])
    ->middleware(['auth', 'rol:coordinador']);

Route::get('/coordinador/solicitud/{id}/pdf', [CoordinadorController::class, 'exportarPdf'])
    ->middleware(['auth', 'rol:coordinador']);

// Rutas para admin
Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'rol:admin']);

// Ruta para ver el detalle de una solicitud específica para el admin
Route::get('/admin/solicitud/{id}', [AdminController::class, 'verDetalle'])
    ->middleware(['auth', 'rol:admin']);

// Ruta para finalizar la solicitud (solo si el admin la tiene en pendiente_admin)
Route::post('/admin/solicitud/{id}/finalizar', [AdminController::class, 'finalizar'])
    ->middleware(['auth', 'rol:admin']);

Route::get('/admin/solicitud/{id}/pdf', [AdminController::class, 'exportarPdf'])
    ->middleware(['auth', 'rol:admin']);

Route::post('/admin/periodos/{id}/actualizar', [AdminController::class, 'actualizarPeriodo'])
    ->middleware(['auth', 'rol:admin']);

Route::get('/admin/periodos', [AdminController::class, 'periodos'])
    ->middleware(['auth', 'rol:admin']);

Route::get('/admin/estadisticas', [AdminController::class, 'estadisticas'])
    ->middleware(['auth', 'rol:admin']);

Route::get('/admin/buscar-estudiante', [AdminController::class, 'buscarEstudiante'])
    ->middleware(['auth', 'rol:admin']);

Route::post('/admin/ciclos/{id}/actualizar', [AdminController::class, 'actualizarCiclo'])
    ->middleware(['auth', 'rol:admin']);

// Rutas para servir evidencias desde Google Cloud Storage
Route::get('/evidencia/{id}/ver', [EvidenciaController::class, 'ver'])
    ->middleware('auth')->name('evidencia.ver');

Route::get('/evidencia/{id}/descargar', [EvidenciaController::class, 'descargar'])
    ->middleware('auth')->name('evidencia.descargar');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');