<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Helpers\CounterHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventarioCrearController extends Controller
{
    public function index()
    {
        $count = CounterHelper::incrementCounter('inventarios/crear');
        
        $user = Auth::user();
        // Obtener roles y permisos del usuario
        $roles = DB::table('usuario_rol_permiso')
            ->join('rol', 'usuario_rol_permiso.id_rol', '=', 'rol.id')
            ->where('usuario_rol_permiso.id_user', $user->id)
            ->pluck('rol.nombre')
            ->toArray();

        $permisos = DB::table('usuario_rol_permiso')
            ->join('permiso', 'usuario_rol_permiso.id_permiso', '=', 'permiso.id')
            ->where('usuario_rol_permiso.id_user', $user->id)
            ->pluck('permiso.nombre')
            ->toArray();

        return Inertia::render('Admin/Inventarios/IndexCrear', [
            'userRoles' => $roles,
            'userPermissions' => $permisos,
            'count' => $count
        ]);
    }
}
