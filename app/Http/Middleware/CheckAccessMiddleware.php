<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class CheckAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles, $permissions)
    {
        // Convertir los permisos en un array
        $permissions = explode(',', $permissions);

        // Convertir los permisos en un array
        $roles = explode(',', $roles);

        // Verificar si el usuario tiene el rol y el permiso especificados
        if (!Gate::allows('check-access', [$roles, $permissions])) {
            return response()->json(['message' => 'No tienes permiso para acceder a esta página.'], 403);
        }

        return $next($request);
    }
}
