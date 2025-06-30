<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RoleControler extends Controller
{
    public function checkUserRole(Request $request)
    {
        $throttleKey = 'login_attempts:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Has excedido el número de intentos. Restablece tu contraseña.']);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $user = Auth::user();

            // Obtener todos los roles y permisos del usuario
            $roles = DB::table('usuario_rol_permiso')
                ->join('rol', 'usuario_rol_permiso.id_rol', '=', 'rol.id')
                ->where('usuario_rol_permiso.id_user', $user->id)
                ->pluck('rol.nombre') // Devuelve una lista de roles
                ->toArray();

            $permisos = DB::table('usuario_rol_permiso')
                ->join('permiso', 'usuario_rol_permiso.id_permiso', '=', 'permiso.id')
                ->where('usuario_rol_permiso.id_user', $user->id)
                ->pluck('permiso.nombre') // Devuelve una lista de permisos
                ->toArray();

            // Prioridad de acceso: si tiene "administrador" y "panel_admin", ir al dashboard admin
            if (in_array('administrador', $roles) && in_array('panel_admin', $permisos)) {
                return redirect()->route('admin.dashboard');
            }

            // Si solo es cliente, lo envía a la vista de usuario normal
            if (in_array('cliente', $roles) && in_array('vista_cliente', $permisos)) {
                return redirect()->route('user.home');
            }

            // Encargado de las ventas diarias
            if (in_array('vendedor', $roles) && in_array('ventas_diarias', $permisos)) {
                return redirect()->route('admin.venta.index');
            }

            // Encargado de inventarios
            if (in_array('encargado_inv', $roles) && in_array('ajustar_inv', $permisos)) {
                return redirect()->route('admin.inventarios.index');
            }

            // Si el usuario tiene solo el rol "encargado_reportes" y permiso "ajustar_reportes"
            if (count($roles) === 1 && in_array('encargado_reportes', $roles) && in_array('ajustar_reportes', $permisos)) {
                // Depurar antes de enviar a Vue
                return redirect()->route('index.reporte');
            }


            return redirect()->route('login')->withErrors(['email' => 'Acceso no autorizado.']);
        }

        RateLimiter::hit($throttleKey, 60);
        $remainingAttempts = RateLimiter::remaining($throttleKey, 3);

        return back()->withErrors([
            'email' => "Credenciales incorrectas. Intentos restantes: $remainingAttempts",
        ]);
    }


    public function assignPermissionToRole(Request $request)
    {
        // Validar los datos entrantes
        $request->validate([
            'id_rol' => 'required|exists:rol,id',
            'id_permiso' => 'required|exists:permiso,id',
        ]);

        // Insertar el permiso para el rol
        DB::table('rol_permiso')->insert([
            'fecha_asignacion' => now(),
            'id_rol' => $request->input('id_rol'),
            'id_permiso' => $request->input('id_permiso')
        ]);

        return response()->json(['message' => 'Permiso asignado correctamente'], 201);
    }
}
