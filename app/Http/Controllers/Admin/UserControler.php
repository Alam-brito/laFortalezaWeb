<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CounterHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class UserControler extends Controller
{
    public function index()
    {
        // Obtener usuarios con sus roles y permisos
        $users = DB::table('users')
            ->leftJoin('usuario_rol_permiso', 'users.id', '=', 'usuario_rol_permiso.id_user')
            ->leftJoin('rol', 'usuario_rol_permiso.id_rol', '=', 'rol.id')
            ->leftJoin('permiso', 'usuario_rol_permiso.id_permiso', '=', 'permiso.id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw("STRING_AGG(DISTINCT rol.nombre, ', ') as roles"), // Usar STRING_AGG en lugar de GROUP_CONCAT
                DB::raw("STRING_AGG(DISTINCT permiso.nombre, ', ') as permisos") // Usar STRING_AGG en lugar de GROUP_CONCAT
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('users.id', 'desc')
            ->get();

        // Obtener todos los roles y permisos disponibles
        $roles = DB::table('rol')->select('id', 'nombre')->get();
        $permisos = DB::table('permiso')->select('id', 'nombre')->get();
        $count = CounterHelper::incrementCounter('usuarios');

        return Inertia::render('Admin/Usuarios/Usuarios', [
            'users' => $users->map(function ($user) {
                $user->roles = !empty($user->roles) ? explode(', ', $user->roles) : [];
                $user->permisos = !empty($user->permisos) ? explode(', ', $user->permisos) : [];
                return $user;
            }),
            'roles' => $roles,
            'permisos' => $permisos,
            'count' => $count
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validar la entrada
        $validated = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:rol,id',
            'permisos' => 'nullable|array',
            'permisos.*' => 'string', // Recibe nombres de permisos
        ]);

        // Obtener los IDs de los permisos seleccionados en el frontend
        $permisoIds = DB::table('permiso')
            ->whereIn('nombre', $validated['permisos'] ?? []) // Si no hay permisos, evita error
            ->pluck('id')
            ->toArray();

        foreach ($validated['roles'] as $rolId) {
            // **Eliminar los permisos que fueron desmarcados**
            if (!empty($permisoIds)) {
                DB::table('usuario_rol_permiso')
                    ->where('id_user', $id)
                    ->where('id_rol', $rolId)
                    ->whereNotIn('id_permiso', $permisoIds) // Solo elimina los permisos que fueron desmarcados
                    ->delete();
            } else {
                // Si no hay permisos seleccionados, eliminar TODOS los permisos del usuario para ese rol
                DB::table('usuario_rol_permiso')
                    ->where('id_user', $id)
                    ->where('id_rol', $rolId)
                    ->delete();
            }

            // **Insertar nuevos permisos si no existen**
            foreach ($permisoIds as $permisoId) {
                // Verificar si el rol y permiso existen en rol_permiso
                $existeRolPermiso = DB::table('rol_permiso')
                    ->where('id_rol', $rolId)
                    ->where('id_permiso', $permisoId)
                    ->exists();

                if (!$existeRolPermiso) {
                    // Insertamos en `rol_permiso` si no existe la relación
                    DB::table('rol_permiso')->insert([
                        'id_rol' => $rolId,
                        'id_permiso' => $permisoId,
                        'fecha_asignacion' => now(),
                    ]);
                }

                // **Verificar si el usuario ya tiene esta combinación de rol y permiso**
                $existeUsuarioRolPermiso = DB::table('usuario_rol_permiso')
                    ->where('id_user', $id)
                    ->where('id_rol', $rolId)
                    ->where('id_permiso', $permisoId)
                    ->exists();

                if (!$existeUsuarioRolPermiso) {
                    // Insertar en `usuario_rol_permiso` si la relación no existe
                    DB::table('usuario_rol_permiso')->insert([
                        'id_user' => $id,
                        'id_rol' => $rolId,
                        'id_permiso' => $permisoId,
                    ]);
                }
            }

            // **Si el usuario no tiene permisos, asegurarse de que el rol sigue asignado**
            $existeRol = DB::table('usuario_rol_permiso')
                ->where('id_user', $id)
                ->where('id_rol', $rolId)
                ->exists();

            if (!$existeRol) {
                DB::table('usuario_rol_permiso')->insert([
                    'id_user' => $id,
                    'id_rol' => $rolId,
                    'id_permiso' => null, // Permitimos que id_permiso sea NULL si no hay permisos
                ]);
            }
        }
        return redirect()->route('admin.usuarios.index')->with('success', 'Rol y permisos actualizados correctamente.');
    }




    public function destroy($id)
    {
        // Eliminar roles y permisos relacionados
        DB::table('usuario_rol_permiso')->where('id_user', $id)->delete();

        // Eliminar el usuario
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado con éxito.');
    }

    public function storeRol(Request $request)
    {
        $request->validate(['nombre' => 'required|unique:rol,nombre|max:30']);
        DB::table('rol')->insert(['nombre' => $request->nombre]);
        return back()->with('success', 'Rol agregado correctamente.');
    }

    public function storePermiso(Request $request)
    {
        $request->validate(['nombre' => 'required|unique:permiso,nombre|max:30']);
        DB::table('permiso')->insert(['nombre' => $request->nombre]);
        return back()->with('success', 'Permiso agregado correctamente.');
    }

    public function destroyRol($id)
    {
        DB::table('rol')->where('id', $id)->delete();
        return back()->with('success', 'Rol eliminado correctamente.');
    }

    public function destroyPermiso($id)
    {
        DB::table('permiso')->where('id', $id)->delete();
        return back()->with('success', 'Permiso eliminado correctamente.');
    }

    public function removeRole($userId, $rolId)
    {
        // Verificar si el usuario tiene ese rol asignado
        $existe = DB::table('usuario_rol_permiso')
            ->where('id_user', $userId)
            ->where('id_rol', $rolId)
            ->exists();

        if (!$existe) {
            return response()->json(['error' => 'El usuario no tiene este rol asignado.'], 404);
        }

        // Eliminar el rol del usuario
        DB::table('usuario_rol_permiso')
            ->where('id_user', $userId)
            ->where('id_rol', $rolId)
            ->delete();

        return redirect()->route('admin.usuarios.index')->with('success', 'Rol eliminado con éxito.');
    }
}
