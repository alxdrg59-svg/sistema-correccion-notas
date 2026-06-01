<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/prueba-db', function () {
    try {
        $usuarios = DB::table('usuarios')->get();
        return $usuarios;
    } catch (\Exception $e) {
        return "Error al conectar: " . $e->getMessage();
    }
});

// Ruta para mostrar el formulario
Route::get('/login', function () {
    return view('login');
})->name('login');

// Ruta para procesar los datos cuando des clic al botón
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/encriptar-mi-clave', function () {
    // Buscamos a tu usuario por correo (ajusta el correo si es otro)
    $user = User::where('correo', 'luis.m@utec.com')->first();
    
    if ($user) {
        $user->password = Hash::make('1234'); // Aquí la encriptamos
        $user->save();
        return "Contraseña actualizada para Luis. ¡Ya puedes intentar el login!";
    }
    
    return "Usuario no encontrado.";
});
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


// Rutas para coordinador
Route::get('/coordinador/dashboard', [CoordinadorController::class, 'index'])
    ->middleware(['auth', 'rol:coordinador']);

// Ruta para ver el detalle de una solicitud específica para el coordinador
Route::get('/coordinador/solicitud/{id}', [CoordinadorController::class, 'verDetalle'])
    ->middleware(['auth', 'rol:coordinador']);

    // Ruta para procesar la decisión del coordinador (aprobar/rechazar)
Route::post('/coordinador/solicitud/{id}/decision', [CoordinadorController::class, 'procesarDecision'])
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

Route::post('/admin/periodos/{id}/toggle', [AdminController::class, 'togglePeriodo'])
    ->middleware(['auth', 'rol:admin']);

Route::post('/admin/ciclos/{id}/actualizar', [AdminController::class, 'actualizarCiclo'])
    ->middleware(['auth', 'rol:admin']);

// Ruta para servir evidencias desde GCS
Route::get('/evidencia/{id}/ver', function ($id) {
    $evidencia = DB::table('evidencias')->where('id', $id)->first();
    if (!$evidencia) { abort(404); }

    $disk = \Illuminate\Support\Facades\Storage::disk('gcs');
    if (!$disk->exists($evidencia->archivo)) { abort(404); }

    $extension = strtolower(pathinfo($evidencia->archivo, PATHINFO_EXTENSION));
    $mimeTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response($disk->get($evidencia->archivo), 200)
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'inline; filename="' . basename($evidencia->archivo) . '"');
})->middleware('auth')->name('evidencia.ver');

Route::get('/evidencia/{id}/descargar', function ($id) {
    $evidencia = DB::table('evidencias')->where('id', $id)->first();
    if (!$evidencia) { abort(404); }

    $disk = \Illuminate\Support\Facades\Storage::disk('gcs');
    if (!$disk->exists($evidencia->archivo)) { abort(404); }

    $extension = strtolower(pathinfo($evidencia->archivo, PATHINFO_EXTENSION));
    $mimeTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response($disk->get($evidencia->archivo), 200)
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'attachment; filename="' . basename($evidencia->archivo) . '"');
})->middleware('auth')->name('evidencia.descargar');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');