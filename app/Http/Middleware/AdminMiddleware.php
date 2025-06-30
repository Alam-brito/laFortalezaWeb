<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isAdmin) {
            return $next($request);
        }

        return redirect()->route('user.home'); // Redirige al home si no es administrador
    }*/

    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Por favor inicia sesión.');
        }

        $userId = Auth::user()->id;

        // Convertir los permisos a un array (en caso de recibir múltiples)
        $permissions = explode('|', $permission);

        // Verifica si el usuario tiene el permiso necesario
        $hasPermission = DB::table('usuario_rol_permiso')
            ->join('rol_permiso', 'usuario_rol_permiso.id_rol', '=', 'rol_permiso.id_rol')
            ->join('permiso', 'rol_permiso.id_permiso', '=', 'permiso.id')
            ->where('usuario_rol_permiso.id_user', $userId)
            ->whereIn('permiso.nombre', $permissions)
            ->exists();
            
        // Detener y mostrar si el usuario tiene permiso
        /*dd([
            'permission' => $permissions,
            'hasPermission' => $hasPermission,
            'usuario' => $userId,
        ]);*/

        if (!$hasPermission) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
