<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VentasController extends Controller
{
    public function index()
    {
        try {
            // Obtener productos con relaciones usando consultas con DB
            $productos = DB::table('producto')
                ->select(
                    'producto.id',
                    'producto.nombre',
                    'producto.descripcion',
                    'producto.precio',
                    'producto.imagen',
                    'producto.id_categoria',
                    'producto.id_promocion',
                    'categoria.nombre as categoria_nombre'
                )
                ->leftJoin('categoria', 'producto.id_categoria', '=', 'categoria.id')
                ->get();

            // Obtener stock de productos
            $productosConStock = [];
            foreach ($productos as $producto) {
                Log::info('Procesando producto: ' . $producto->nombre);
                Log::info('ID de promoción: ' . ($producto->id_promocion ?? 'No tiene'));
                // Obtener almacenes y stock del producto
                $almacenesProducto = DB::table('producto_almacen')
                    ->select(
                        'almacen.id',
                        'almacen.nombre',
                        'producto_almacen.stock'
                    )
                    ->join('almacen', 'producto_almacen.id_almacen', '=', 'almacen.id')
                    ->where('producto_almacen.id_producto', $producto->id)
                    ->get();

                // Calcular stock total
                $stockTotal = $almacenesProducto->sum('stock');

                // Verificar si tiene promoción vigente
                $promocion = null;
                if ($producto->id_promocion) {
                    $promocion = DB::table('promocion')
                        ->where('id', $producto->id_promocion)
                        ->whereDate('fecha_inicio', '<=', Carbon::now())
                        ->whereDate('fecha_final', '>=', Carbon::now())
                        ->first();

                    // Añadir log de la promoción encontrada
                    Log::info('Promoción encontrada para el producto ' . $producto->nombre, [
                        'promocion' => $promocion
                    ]);
                }

                // Calcular precio final con descuento si aplica
                $precioFinal = $producto->precio;
                $descuento = 0;
                if ($promocion) {
                    $descuento = $promocion->descuento;
                    $precioFinal = $producto->precio * (1 - ($descuento / 100));
                }

                Log::info('Precio calculado para el producto ' . $producto->nombre, [
                    'precio_original' => $producto->precio,
                    'descuento' => $descuento,
                    'precio_final' => $precioFinal
                ]);


                // Agregar producto con datos adicionales
                $productosConStock[] = [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'precioFinal' => round($precioFinal, 2),
                    'imagen' => $producto->imagen,
                    'categoria' => $producto->categoria_nombre ?? 'Sin categoría',
                    'id_categoria' => $producto->id_categoria,
                    'stock' => $stockTotal,
                    'almacenes' => $almacenesProducto,
                    'enPromocion' => $promocion ? true : false,
                    'descuento' => $promocion ? $promocion->descuento : 0
                ];
            }

            // Obtener categorías para filtros
            $categorias = DB::table('categoria')
                ->select('id', 'nombre', 'descripcion')
                ->orderBy('nombre', 'asc')
                ->get();

            // Obtener almacenes para selección
            $almacenes = DB::table('almacen')
                ->select('id', 'nombre', 'ubicacion')
                ->orderBy('nombre', 'asc')
                ->get();

            // Ya no necesitamos enviar todos los clientes
            return Inertia::render('Admin/VentaLocal/Index', [
                'productos' => $productosConStock,
                'categorias' => $categorias,
                'almacenes' => $almacenes
            ]);
        } catch (\Exception $e) {
            Log::error('Error en VentasController@index: ' . $e->getMessage());

            return Inertia::render('Admin/VentaLocal/Index', [
                'productos' => [],
                'categorias' => [],
                'almacenes' => [],
                'error' => 'Ocurrió un error al cargar los datos: ' . $e->getMessage()
            ]);
        }
    }

    public function buscarProductos(Request $request)
    {
        try {
            $query = $request->input('query');
            $categoriaId = $request->input('categoria');
            $almacenId = $request->input('almacen');

            Log::info('Búsqueda de productos', [
                'query' => $query,
                'categoria' => $categoriaId,
                'almacen' => $almacenId
            ]);

            // Construir la consulta base
            $productosQuery = DB::table('producto')
                ->select(
                    'producto.id',
                    'producto.nombre',
                    'producto.descripcion',
                    'producto.precio',
                    'producto.imagen',
                    'producto.id_categoria',
                    'producto.id_promocion',
                    'categoria.nombre as categoria_nombre'
                )
                ->leftJoin('categoria', 'producto.id_categoria', '=', 'categoria.id');

            // Aplicar filtro de búsqueda por texto
            if ($query) {
                $productosQuery->where(function ($q) use ($query) {
                    $q->where('producto.nombre', 'like', "%{$query}%")
                        ->orWhere('producto.descripcion', 'like', "%{$query}%");
                });
            }

            // Aplicar filtro por categoría
            if ($categoriaId) {
                $productosQuery->where('producto.id_categoria', $categoriaId);
            }

            // Si hay filtro por almacén, solo incluir productos que tengan stock en ese almacén
            if ($almacenId) {
                $productosQuery->join('producto_almacen', function ($join) use ($almacenId) {
                    $join->on('producto.id', '=', 'producto_almacen.id_producto')
                        ->where('producto_almacen.id_almacen', '=', $almacenId);
                });
            }

            $productos = $productosQuery->distinct()->get();
            Log::info('Productos encontrados: ' . count($productos));

            // Procesar resultados para incluir stock e información adicional
            $resultados = [];
            foreach ($productos as $producto) {
                // Obtener información de almacenes y stock
                if ($almacenId) {
                    // Si hay filtro de almacén, solo obtenemos el stock de ese almacén
                    $almacenesProducto = DB::table('producto_almacen')
                        ->select(
                            'almacen.id',
                            'almacen.nombre',
                            'producto_almacen.stock'
                        )
                        ->join('almacen', 'producto_almacen.id_almacen', '=', 'almacen.id')
                        ->where('producto_almacen.id_producto', $producto->id)
                        ->where('producto_almacen.id_almacen', $almacenId)
                        ->get();

                    $stockTotal = $almacenesProducto->sum('stock');
                } else {
                    // Si no hay filtro, obtenemos todos los almacenes
                    $almacenesProducto = DB::table('producto_almacen')
                        ->select(
                            'almacen.id',
                            'almacen.nombre',
                            'producto_almacen.stock'
                        )
                        ->join('almacen', 'producto_almacen.id_almacen', '=', 'almacen.id')
                        ->where('producto_almacen.id_producto', $producto->id)
                        ->get();

                    $stockTotal = $almacenesProducto->sum('stock');
                }

                // Verificar si tiene promoción vigente
                $promocion = null;
                if ($producto->id_promocion) {
                    $promocion = DB::table('promocion')
                        ->where('id', $producto->id_promocion)
                        ->whereDate('fecha_inicio', '<=', Carbon::now())
                        ->whereDate('fecha_final', '>=', Carbon::now())
                        ->first();
                }

                // Calcular precio final con descuento si aplica
                $precioFinal = $producto->precio;
                $descuento = 0;
                if ($promocion) {
                    $descuento = $promocion->descuento;
                    $precioFinal = $producto->precio - ($producto->precio * $descuento / 100);
                }

                $resultados[] = [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'precioFinal' => round($precioFinal, 2),
                    'imagen' => $producto->imagen,
                    'categoria' => $producto->categoria_nombre ?? 'Sin categoría',
                    'id_categoria' => $producto->id_categoria,
                    'stock' => $stockTotal,
                    'almacenes' => $almacenesProducto,
                    'enPromocion' => $promocion ? true : false, // Verificación más precisa
                    'descuento' => $promocion ? $promocion->descuento : 0
                ];
            }

            Log::info('Resultados procesados: ' . count($resultados));
            return response()->json($resultados);
        } catch (\Exception $e) {
            Log::error('Error en buscarProductos: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al buscar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function procesarVenta(Request $request)
    {
        // Validar datos
        $request->validate([
            'nombreCliente' => 'required|string|min:2', // Validar el nombre en lugar del ID
            'almacenId' => 'required|exists:almacen,id',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'tipoPago' => 'required|string',
            'total' => 'required|numeric|min:0',
        ]);

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Buscar o crear un cliente con el nombre proporcionado
            $clienteId = $this->buscarOCrearCliente($request->nombreCliente);

            // Crear pago
            $pagoId = DB::table('pago')->insertGetId([
                'tipo_pago' => $request->tipoPago,
                'monto' => $request->total,
                'estado' => 'completado',
                'fecha' => Carbon::now()
            ]);

            // Crear venta
            $ventaId = DB::table('venta')->insertGetId([
                'total' => $request->total,
                'fecha' => Carbon::now()->toDateString(),
                'id_cliente' => $clienteId,
                'id_pago' => $pagoId
            ]);

            // Crear detalles de venta y actualizar stock
            foreach ($request->productos as $item) {
                // Verificar stock disponible
                $stockActual = DB::table('producto_almacen')
                    ->where('id_producto', $item['id'])
                    ->where('id_almacen', $request->almacenId)
                    ->value('stock');

                if ($stockActual === null || $stockActual < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para el producto ID: {$item['id']}");
                }

                // Crear detalle de venta
                DB::table('detalle_venta')->insert([
                    'id_producto' => $item['id'],
                    'id_almacen' => $request->almacenId,
                    'id_venta' => $ventaId,
                    'cantidad' => $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null
                ]);

                // Actualizar stock
                DB::table('producto_almacen')
                    ->where('id_producto', $item['id'])
                    ->where('id_almacen', $request->almacenId)
                    ->decrement('stock', $item['cantidad']);
            }

            // Confirmar transacción
            DB::commit();

            // Registrar venta exitosa
            Log::info('Venta procesada correctamente', [
                'venta_id' => $ventaId,
                'cliente_id' => $clienteId,
                'total' => $request->total,
                'productos' => count($request->productos)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Venta procesada correctamente',
                'venta_id' => $ventaId
            ]);
        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            DB::rollBack();

            Log::error('Error al procesar venta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca un cliente por nombre o lo crea si no existe
     */
    private function buscarOCrearCliente($nombreCliente)
    {
        // Limpiar y normalizar el nombre
        $nombreCliente = trim($nombreCliente);

        // Buscar si ya existe un usuario con ese nombre
        $usuario = DB::table('users')
            ->where('name', $nombreCliente)
            ->first();

        if ($usuario) {
            // Si existe el usuario, obtener su cliente asociado
            $cliente = DB::table('cliente')
                ->where('id', $usuario->id)
                ->first();

            return $cliente->id;
        }

        // Si no existe, crear un nuevo usuario (y el trigger creará el cliente)
        $userId = DB::table('users')->insertGetId([
            'name' => $nombreCliente,
            'email' => 'cliente_' . time() . '@example.com', // Email temporal
            'password' => bcrypt('password_temporal') // Contraseña temporal
        ]);

        // El trigger debería haber creado el cliente, ahora lo buscamos
        $cliente = DB::table('cliente')
            ->where('id', $userId)
            ->first();

        return $cliente->id;
    }

    /**
     * Obtiene los datos para generar la factura
     */
    public function obtenerFactura($id)
    {
        try {
            // Obtener datos de la venta
            $venta = DB::table('venta')
                ->select(
                    'venta.id',
                    'venta.total',
                    'venta.fecha',
                    'cliente.id as cliente_id',
                    'users.name as cliente_nombre', // Changed from cliente.nombre
                    'users.email as cliente_email', // Changed from cliente.email
                    'pago.tipo_pago'
                )
                ->join('cliente', 'venta.id_cliente', '=', 'cliente.id')
                ->join('users', 'cliente.id', '=', 'users.id') // Added join to the users table
                ->join('pago', 'venta.id_pago', '=', 'pago.id')
                ->where('venta.id', $id)
                ->first();

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada'
                ], 404);
            }

            // En el método obtenerFactura(), dentro de la consulta de detalles:
            $detalles = DB::table('detalle_venta')
                ->select(
                    'detalle_venta.cantidad', // <-- Se eliminó detalle_venta.id
                    'detalle_venta.descripcion', // Descripcion del detalle de la venta
                    'producto.nombre as producto_nombre',
                    'producto.precio',
                    'almacen.nombre as almacen_nombre'
                )
                ->join('producto', 'detalle_venta.id_producto', '=', 'producto.id')
                ->join('almacen', 'detalle_venta.id_almacen', '=', 'almacen.id')
                ->where('detalle_venta.id_venta', $id)
                ->get();

            // Calcular subtotales para cada item
            $items = [];
            foreach ($detalles as $detalle) {
                $items[] = [
                    'producto' => $detalle->producto_nombre,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    'almacen' => $detalle->almacen_nombre,
                    'subtotal' => $detalle->precio * $detalle->cantidad
                ];
            }

            // Formatear datos para la factura
            $factura = [
                'id' => $venta->id,
                'fecha' => Carbon::parse($venta->fecha)->format('d/m/Y'),
                'hora' => Carbon::now()->format('H:i:s'),
                'cliente' => [
                    'id' => $venta->cliente_id,
                    'nombre' => $venta->cliente_nombre,
                    'email' => $venta->cliente_email
                ],
                'items' => $items,
                'total' => $venta->total,
                'tipoPago' => $this->formatearTipoPago($venta->tipo_pago)
            ];

            return response()->json($factura);
        } catch (\Exception $e) {
            Log::error('Error al obtener factura: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea el tipo de pago para mostrar en la factura
     */
    private function formatearTipoPago($tipoPago)
    {
        $formatosPago = [
            'efectivo' => 'Efectivo',
            'tarjeta' => 'Tarjeta de crédito/débito',
            'transferencia' => 'Transferencia bancaria'
        ];

        return $formatosPago[$tipoPago] ?? ucfirst($tipoPago);
    }
}
