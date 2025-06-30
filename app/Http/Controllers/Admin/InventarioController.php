<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Helpers\CounterHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->query('search'); // Variable para el buscador
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

        $count = CounterHelper::incrementCounter('detalle_ajuste');
        $inventarios = DB::table('detalle_ajuste')
            ->join('producto', 'detalle_ajuste.id_producto', '=', 'producto.id')
            ->join('almacen', 'detalle_ajuste.id_almacen', '=', 'almacen.id')
            ->join('ajuste_inventario', 'detalle_ajuste.id_ajuste_inventario', '=', 'ajuste_inventario.id')
            ->select(
                'producto.nombre as producto_nombre',
                'almacen.nombre as almacen_nombre',
                'ajuste_inventario.tipo as tipo_ajuste',
                'detalle_ajuste.cantidad',
                'producto_almacen.stock',
                DB::raw('ROW_NUMBER() OVER () as detalle_id'), // Genera un identificador temporal
                'ajuste_inventario.fecha as fecha_actualizacion'
            )
            ->join('producto_almacen', function ($join) {
                $join->on('producto_almacen.id_producto', '=', 'producto.id')
                    ->on('producto_almacen.id_almacen', '=', 'almacen.id');
            })
            ->when($search, function ($query, $search) {
                $query->where('producto.nombre', 'like', "%$search%")
                    ->orWhere('almacen.nombre', 'like', "%$search%");
            })
            ->orderBy('detalle_id', 'desc')
            ->get();

        return Inertia::render('Admin/Inventarios/Index', [
            'inventarios' => $inventarios,
            'count' => $count,
            'userRoles' => $roles,
            'userPermissions' => $permisos,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'required|string',
            'id_categoria' => 'required|exists:categoria,id',
            'id_almacen' => 'required|exists:almacen,id',
            'stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Registrar el producto
            $productoId = DB::table('producto')->insertGetId([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'imagen' => $request->imagen,
                'id_categoria' => $request->id_categoria,
            ]);

            // Asociar el producto con un almacén
            DB::table('producto_almacen')->insert([
                'id_producto' => $productoId,
                'id_almacen' => $request->id_almacen,
                'stock' => $request->stock,
            ]);
        });

        return redirect()->route('admin.inventarios.index')->with('success', 'Producto agregado al inventario con éxito');
    }

    public function ajustarInventario(Request $request)
    {
        // Validar los datos
        $request->validate([
            'tipo' => 'required|string|in:ingreso,egreso',
            'glosa' => 'required|string|max:80', // Glosa del ajuste
            'productos' => 'required|array|min:1', // Validación para recibir múltiples productos
            'productos.*.id_producto' => 'required|exists:producto,id',
            'productos.*.id_almacen' => 'required|exists:almacen,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // **Crear un solo ajuste de inventario**
            $ajusteId = DB::table('ajuste_inventario')->insertGetId([
                'tipo' => $request->tipo,
                'glosa' => $request->glosa,
                'fecha' => now(),
                'id_user' => Auth::user()->id,
            ]);

            foreach ($request->productos as $producto) {
                // **Insertar cada producto en detalle_ajuste**
                DB::table('detalle_ajuste')->insert([
                    'id_producto' => $producto['id_producto'],
                    'id_almacen' => $producto['id_almacen'],
                    'cantidad' => $producto['cantidad'],
                    'id_ajuste_inventario' => $ajusteId,
                ]);

                // **Actualizar stock en producto_almacen en una sola operación**
                DB::table('producto_almacen')
                    ->where('id_producto', $producto['id_producto'])
                    ->where('id_almacen', $producto['id_almacen'])
                    ->update([
                        'stock' => DB::raw("stock " . ($request->tipo === 'ingreso' ? "+" : "-") . " {$producto['cantidad']}")
                    ]);
            }
        });

        return redirect()->route('admin.inventarios.index')->with('success', 'Inventario ajustado correctamente');
    }
}
