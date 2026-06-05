<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\AdminController;

class ActualizarAnioAcademico
{
    public function handle(Request $request, Closure $next)
    {
        $cacheKey = 'anio_academico_actualizado_' . now()->format('Y');

        if (!Cache::has($cacheKey)) {
            AdminController::autoActualizarAnio();
            Cache::put($cacheKey, true, now()->endOfDay());
        }

        return $next($request);
    }
}
