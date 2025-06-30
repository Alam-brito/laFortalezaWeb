<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Helpers\CounterHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $roles = ['administrador'];
        $permissions = ['panel_admin', 'vista_cliente'];
        if (Gate::denies('check-access', [$roles, $permissions])) {
            return redirect()->route('admin.login');
        }

        $count = CounterHelper::incrementCounter('dashboard');
        // Consultar las ventas más altas
        $ventasMasAltas = DB::table('venta')
            ->join('users', 'venta.id_cliente', '=', 'users.id')
            ->select('users.name as cliente', 'venta.total', 'venta.fecha')
            ->orderBy('venta.total', 'desc')
            ->take(5)
            ->get();

        // Consultar los productos más vendidos
        $productosMasVendidos = DB::table('detalle_venta')
            ->join('producto', 'detalle_venta.id_producto', '=', 'producto.id')
            ->select('producto.nombre', DB::raw('SUM(detalle_venta.cantidad) as total_vendido'))
            ->groupBy('producto.nombre')
            ->orderBy('total_vendido', 'desc')
            ->take(5)
            ->get();

        // Consultar ingresos totales de la última semana
        $ingresosSemanales = DB::table('venta')
            ->select(DB::raw('SUM(total) as ingresos'), DB::raw('DATE(fecha) as fecha'))
            ->where('fecha', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy('fecha', 'asc')
            ->get();

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

        // Total de ventas y productos vendidos
        $resumen = [
            'totalVentas' => DB::table('venta')->count(),
            'totalProductosVendidos' => DB::table('detalle_venta')->sum('cantidad'),
        ];

        return Inertia::render('Admin/Dashboard', [
            'ventasMasAltas' => $ventasMasAltas,
            'productosMasVendidos' => $productosMasVendidos,
            'ingresosSemanales' => $ingresosSemanales,
            'resumen' => $resumen,
            'count' => $count,
            'userRoles' => $roles,
            'userPermissions' => $permisos,
        ]);
        // Depurar antes de enviar a Vue
        //dd($roles, $permisos);
    }
}
