<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Helpers\CounterHelper;
use Exception;

class CarControler extends Controller
{
    public function index()
    {
        $count = CounterHelper::incrementCounter('pago');
        $userId = Auth::id();

        // Obtener el carrito pendiente del usuario
        $carritoPendiente = DB::table('carritos_pendientes')
            ->where('id_users', $userId)
            ->orderByDesc('created_at')
            ->first();

        if (!$carritoPendiente) {
            return redirect()->route('user.home')->with('error', 'No tienes un carrito pendiente.');
        }

        // Decodificar productos y agregar el id_almacen desde la base de datos
        $productos = collect(json_decode($carritoPendiente->productos, true))
            ->map(function ($producto) {
                // Obtener el id_almacen relacionado al producto
                $idAlmacen = DB::table('producto_almacen')
                    ->where('id_producto', $producto['id'])
                    ->value('id_almacen');

                return [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $producto['subtotal'],
                    'id_almacen' => $idAlmacen, // Asociar el id_almacen desde la base de datos
                    'imagen' => $producto['imagen'] ?? null,
                ];
            })
            ->toArray();

        // Validar si algún producto no tiene id_almacen
        foreach ($productos as $producto) {
            if (is_null($producto['id_almacen'])) {
                return redirect()->route('user.home')->with('error', 'No se encontró un almacén para el producto: ' . $producto['nombre']);
            }
        }

        // Calcular el total redondeado
        $total = round(collect($productos)->sum('subtotal'), 3);

        // Obtener los datos del usuario autenticado
        $user = Auth::user();

        return Inertia::render('User/CarList', [
            'productos' => $productos ?? [], // Siempre aseguramos que sea un array
            'total' => $total, // Incluir el total en los datos enviados a la vista
            'count' => $count,
            'user' => [
                'id' => $user->id, // Asegúrate de incluir el ID aquí
                'telefono' => $user->telefono ?? null,
                'razon_social' => $user->razon_social ?? null,
                'nit' => $user->nit ?? null,
                'email' => $user->email ?? null,
            ],
        ]);
    }


    public function confirmarPago(Request $request)
    {
        $userId = Auth::id();

        // Obtener el carrito pendiente
        $carritoPendiente = DB::table('carritos_pendientes')
            ->where('id_users', $userId)
            ->first();

        if (!$carritoPendiente) {
            return response()->json(['success' => false, 'message' => 'No se encontró un carrito pendiente.']);
        }

        // Validar carrito y productos
        $carrito = json_decode($carritoPendiente->productos, true);
        foreach ($carrito as $producto) {
            if (empty($producto['id']) || empty($producto['id_almacen']) || empty($producto['cantidad']) || empty($producto['subtotal'])) {
                return response()->json(['success' => false, 'message' => 'Carrito contiene datos incompletos.']);
            }

            // Verificar stock disponible
            $stock = DB::table('producto_almacen')
                ->where('id_producto', $producto['id'])
                ->where('id_almacen', $producto['id_almacen'])
                ->value('stock');

            if ($stock < $producto['cantidad']) {
                return response()->json(['success' => false, 'message' => "Stock insuficiente para el producto ID {$producto['id']} en el almacén ID {$producto['id_almacen']}"]);
            }
        }

        try {
            DB::beginTransaction();

            // Registrar el pago
            $pagoId = DB::table('pago')->insertGetId([
                'tipo_pago' => 'QR',
                'monto' => $request->input('total'),
                'estado' => 'completado',
                'fecha' => now(),
            ]);

            // Registrar la venta
            $ventaId = DB::table('venta')->insertGetId([
                'total' => $request->input('total'),
                'fecha' => now(),
                'id_cliente' => $userId,
                'id_pago' => $pagoId,
            ]);

            // **Registrar ajuste de inventario tipo "egreso"**
            $ajusteId = DB::table('ajuste_inventario')->insertGetId([
                'tipo' => 'egreso',
                'glosa' => 'Venta registrada',
                'fecha' => now(),
                'id_user' => $userId,
            ]);

            // Registrar detalles de venta y actualizar stock
            foreach ($carrito as $producto) {
                DB::table('detalle_venta')->insert([
                    'cantidad' => $producto['cantidad'],
                    'id_producto' => $producto['id'],
                    'id_almacen' => $producto['id_almacen'],
                    'id_venta' => $ventaId,
                ]);

                // **Insertar en detalle_ajuste como egreso**
                DB::table('detalle_ajuste')->insert([
                    'id_producto' => $producto['id'],
                    'id_almacen' => $producto['id_almacen'],
                    'cantidad' => $producto['cantidad'],
                    'id_ajuste_inventario' => $ajusteId,
                ]);

                DB::table('producto_almacen')
                    ->where('id_producto', $producto['id'])
                    ->where('id_almacen', $producto['id_almacen'])
                    ->decrement('stock', $producto['cantidad']);
            }

            // Eliminar el carrito pendiente
            DB::table('carritos_pendientes')->where('id', $carritoPendiente->id)->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pago confirmado y compra registrada.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function Factura()
    {
        try {
            $count = CounterHelper::incrementCounter('factura');
            $userId = Auth::id();

            // **Obtener la última venta del usuario con límite de 1**
            $venta = DB::table('venta')
                ->join('pago', 'venta.id_pago', '=', 'pago.id')
                ->where('venta.id_cliente', $userId)
                ->orderByDesc('venta.id')
                ->select('venta.id', 'venta.total', 'venta.fecha', 'pago.tipo_pago as metodo_pago')
                ->limit(1) //Se asegura de obtener solo una venta
                ->first();

            if (!$venta) {
                return redirect()->route('user.home')->with('error', 'No se encontró una venta registrada.');
            }

            // **Obtener los productos de esa venta optimizando los JOIN**
            $detallesVenta = DB::table('detalle_venta')
                ->join('producto', 'detalle_venta.id_producto', '=', 'producto.id')
                ->leftJoin('promocion', 'producto.id_promocion', '=', 'promocion.id') // Solo si es necesario
                ->where('detalle_venta.id_venta', $venta->id)
                ->select(
                    'producto.nombre as producto',
                    'producto.descripcion',
                    'detalle_venta.cantidad',
                    'producto.precio',
                    DB::raw('(detalle_venta.cantidad * producto.precio) as subtotal'),
                    DB::raw('COALESCE(promocion.descuento, 0) as descuento')
                )
                ->limit(50) //Evita que la consulta cargue demasiados registros
                ->get();

            if ($detallesVenta->isEmpty()) {
                return redirect()->route('user.home')->with('error', 'No se encontraron productos en la venta.');
            }

            // **Cálculo de totales más eficiente**
            $subtotal = $detallesVenta->sum('subtotal');
            $descuentoTotal = $detallesVenta->sum(fn($item) => ($item->descuento / 100) * $item->subtotal);
            $total = $subtotal - $descuentoTotal;

            // **Log para depuración**
            logger()->info("Factura generada", [
                'venta_id' => $venta->id,
                'subtotal' => $subtotal,
                'descuento' => $descuentoTotal,
                'total' => $total,
                'productos' => $detallesVenta->toArray(),
            ]);

            // **Construcción de la factura**
            $factura = [
                'cliente' => Auth::user()->name,
                'email' => Auth::user()->email,
                'fecha' => $venta->fecha,
                'metodo_pago' => $venta->metodo_pago,
                'productos' => $detallesVenta,
                'subtotal' => round($subtotal, 2),
                'descuento' => round($descuentoTotal, 2),
                'total' => round($total, 2),
            ];

            return Inertia::render('User/Factura', [
                'factura' => $factura,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return redirect()->route('user.home')->with('error', 'Error al generar la factura: ' . $e->getMessage());
        }
    }
}
