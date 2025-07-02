<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HistorialControler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $userId = Auth::id();
            
            // Obtener el cliente asociado al usuario
            $cliente = DB::table('cliente')
                ->where('id', $userId)
                ->first();

            if (!$cliente) {
                return Inertia::render('User/Historial', [
                    'historial' => [],
                    'mensaje' => 'No se encontraron datos de cliente.'
                ]);
            }

            // Consulta principal para obtener el historial de ventas
            $historialVentas = DB::table('venta as v')
                ->join('pago as p', 'v.id_pago', '=', 'p.id')
                ->join('cliente as c', 'v.id_cliente', '=', 'c.id')
                ->join('users as u', 'c.id', '=', 'u.id')
                ->select(
                    'v.id as venta_id',
                    'v.total',
                    'v.fecha',
                    'p.tipo_pago',
                    'p.estado as estado_pago',
                    'p.fecha as fecha_pago',
                    'u.name as cliente_nombre'
                )
                ->where('v.id_cliente', $cliente->id)
                ->orderBy('v.fecha', 'desc')
                ->get();

            // Para cada venta, obtener los productos
            $historialCompleto = [];
            
            foreach ($historialVentas as $venta) {
                $productos = DB::table('detalle_venta as dv')
                    ->join('producto as pr', 'dv.id_producto', '=', 'pr.id')
                    ->join('almacen as a', 'dv.id_almacen', '=', 'a.id')
                    ->join('categoria as cat', 'pr.id_categoria', '=', 'cat.id')
                    ->leftJoin('promocion as prom', 'pr.id_promocion', '=', 'prom.id')
                    ->select(
                        'pr.id as producto_id',
                        'pr.nombre as producto_nombre',
                        'pr.descripcion as producto_descripcion',
                        'pr.precio as precio_unitario',
                        'pr.imagen',
                        'dv.cantidad',
                        'dv.descripcion as descripcion_venta',
                        'a.nombre as almacen_nombre',
                        'cat.nombre as categoria_nombre',
                        'prom.descuento',
                        // Calcular subtotal
                        DB::raw('(pr.precio * dv.cantidad) as subtotal'),
                        // Calcular precio con descuento si aplica
                        DB::raw('CASE 
                            WHEN prom.descuento IS NOT NULL 
                            THEN pr.precio * (1 - prom.descuento / 100.0) 
                            ELSE pr.precio 
                        END as precio_final')
                    )
                    ->where('dv.id_venta', $venta->venta_id)
                    ->get();

                // Formatear las imágenes
                $productosFormateados = $productos->map(function ($producto) {
                    $producto->imagen_url = $this->formatImageUrl($producto->imagen);
                    $producto->subtotal_final = $producto->precio_final * $producto->cantidad;
                    return $producto;
                });

                $historialCompleto[] = [
                    'venta' => $venta,
                    'productos' => $productosFormateados,
                    'total_productos' => $productos->count(),
                    'cantidad_total' => $productos->sum('cantidad')
                ];
            }

            return Inertia::render('User/Historial', [
                'historial' => $historialCompleto,
                'cliente_nombre' => $cliente->id ? $historialVentas->first()->cliente_nombre ?? 'Cliente' : 'Cliente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar historial de ventas: ' . $e->getMessage());
            
            return Inertia::render('User/Historial', [
                'historial' => [],
                'error' => 'Error al cargar el historial de ventas.'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();
            
            // Verificar que la venta pertenece al usuario actual
            $venta = DB::table('venta as v')
                ->join('cliente as c', 'v.id_cliente', '=', 'c.id')
                ->where('v.id', $id)
                ->where('c.id', $userId)
                ->select('v.*')
                ->first();

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada o no autorizada.'
                ], 404);
            }

            // Obtener los productos de la venta para restaurar stock
            $detallesVenta = DB::table('detalle_venta')
                ->where('id_venta', $id)
                ->get();

            // Restaurar stock de los productos
            foreach ($detallesVenta as $detalle) {
                DB::table('producto_almacen')
                    ->where('id_producto', $detalle->id_producto)
                    ->where('id_almacen', $detalle->id_almacen)
                    ->increment('stock', $detalle->cantidad);
            }

            // Eliminar detalles de venta
            DB::table('detalle_venta')
                ->where('id_venta', $id)
                ->delete();

            // Eliminar la venta
            DB::table('venta')
                ->where('id', $id)
                ->delete();

            // Actualizar estado del pago
            DB::table('pago')
                ->where('id', $venta->id_pago)
                ->update(['estado' => 'cancelado']);

            DB::commit();

            Log::info('Venta eliminada correctamente', [
                'venta_id' => $id,
                'user_id' => $userId
            ]);

            return redirect()->route('historial.index')->with('success', 'Venta borrada con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar venta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la venta.'
            ], 500);
        }
    }

    /**
     * Formatear URL de imagen
     */
    private function formatImageUrl($imagePath)
    {
        if (!$imagePath) {
            return '/images/no-image.png';
        }

        // Si la imagen comienza con http o https, es una URL completa
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }

        // Si es una imagen de Cloudinary u otro servicio
        if (str_contains($imagePath, 'cloudinary.com') || str_contains($imagePath, 'res.cloudinary.com')) {
            return $imagePath;
        }

        // Asumir que es una ruta local
        return '/storage/' . $imagePath;
    }

    /**
     * Obtener estadísticas del cliente
     */
    public function estadisticas()
    {
        try {
            $userId = Auth::id();
            
            $stats = DB::table('venta as v')
                ->join('cliente as c', 'v.id_cliente', '=', 'c.id')
                ->where('c.id', $userId)
                ->selectRaw('
                    COUNT(*) as total_compras,
                    SUM(v.total) as total_gastado,
                    AVG(v.total) as promedio_por_compra,
                    MAX(v.total) as compra_mayor,
                    MIN(v.total) as compra_menor
                ')
                ->first();

            return response()->json([
                'success' => true,
                'estadisticas' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas.'
            ], 500);
        }
    }
}