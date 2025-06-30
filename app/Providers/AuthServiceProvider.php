<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Define un Gate que valida el rol y el permiso
        Gate::define('check-access', function ($user, $roles, $permissions) {
            $permissions = is_array($permissions) ? $permissions : [$permissions];
            $roles = is_array($roles) ? $roles : [$roles];

            // Obtener los roles y permisos del usuario
            $userRoles = DB::table('usuario_rol_permiso')
                ->join('rol', 'usuario_rol_permiso.id_rol', '=', 'rol.id')
                ->where('usuario_rol_permiso.id_user', $user->id)
                ->pluck('rol.nombre')
                ->toArray();

            $userPermissions = DB::table('usuario_rol_permiso')
                ->join('permiso', 'usuario_rol_permiso.id_permiso', '=', 'permiso.id')
                ->where('usuario_rol_permiso.id_user', $user->id)
                ->pluck('permiso.nombre')
                ->toArray();

            // Prioridad: si es administrador con panel_admin, siempre permite acceso
            if (in_array('administrador', $userRoles) && in_array('panel_admin', $userPermissions)) {
                return true;
            }

            // Si tiene los roles/permisos indicados, permite acceso
            return array_intersect($roles, $userRoles) && array_intersect($permissions, $userPermissions);
        });

        // Compartir roles y permisos con Inertia
        Inertia::share([
            'userRoles' => function () {
                if (Auth::check()) {
                    return DB::table('usuario_rol_permiso')
                        ->join('rol', 'usuario_rol_permiso.id_rol', '=', 'rol.id')
                        ->where('usuario_rol_permiso.id_user', Auth::id())
                        ->pluck('rol.nombre')
                        ->toArray();
                }
                return [];
            },
            'userPermissions' => function () {
                if (Auth::check()) {
                    return DB::table('usuario_rol_permiso')
                        ->join('permiso', 'usuario_rol_permiso.id_permiso', '=', 'permiso.id')
                        ->where('usuario_rol_permiso.id_user', Auth::id())
                        ->pluck('permiso.nombre')
                        ->toArray();
                }
                return [];
            }
        ]);
    }
}
