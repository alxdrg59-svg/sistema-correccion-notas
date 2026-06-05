<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identificador' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $identificador = trim($request->identificador);
        $password = $request->password;
        $autenticado = false;

        $soloDigitos = preg_replace('/[^0-9]/', '', $identificador);
        if (strlen($soloDigitos) === strlen($identificador) && strlen($identificador) > 0 || preg_match('/^\d{2}-\d{4}-\d{4}$/', $identificador)) {
            $carnetSinGuiones = $soloDigitos;
            $user = User::whereRaw("REPLACE(carnet, '-', '') = ?", [$carnetSinGuiones])->first();
            if ($user && Hash::check($password, $user->password)) {
                Auth::login($user);
                $request->session()->regenerate();
                $autenticado = true;
            }
        } else {
            if (Auth::attempt(['correo' => $identificador, 'password' => $password])) {
                $request->session()->regenerate();
                $autenticado = true;
            }
        }

        if ($autenticado) {
            $user = Auth::user();
            return match($user->rol) {
                'admin'       => redirect()->intended('/admin/dashboard'),
                'docente'     => redirect()->intended('/docente/dashboard'),
                'coordinador' => redirect()->intended('/coordinador/dashboard'),
                default       => redirect()->intended('/estudiante/dashboard'),
            };
        }

        return back()->withErrors([
            'identificador' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    // Función para cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}