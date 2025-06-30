<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VentasDiariasController extends Controller
{
    public function index()
    {
        // Configurar la fecha en la zona horaria correcta
        $fechaCarbon = Carbon::now('America/La_Paz');
        $startOfDay = $fechaCarbon->copy()->startOfDay()->setTimezone('UTC');
        $endOfDay = $fechaCarbon->copy()->endOfDay()->setTimezone('UTC');

        try {
            // Consulta optimizada con timezone correcto
            $ventasDiarias = DB::table('venta')
                ->join('users', 'venta.id_cliente', '=', 'users.id')
                ->join('pago', 'venta.id_pago', '=', 'pago.id')
                ->join('detalle_venta', 'venta.id', '=', 'detalle_venta.id_venta')
                ->join('producto', 'detalle_venta.id_producto', '=', 'producto.id')
                ->whereBetween('venta.fecha', [$startOfDay, $endOfDay])
                ->select(
                    'users.name as nombre_cliente',
                    'producto.nombre as nombre_producto',
                    'producto.precio',
                    'detalle_venta.cantidad',
                    DB::raw('producto.precio * detalle_venta.cantidad as subtotal'),
                    'pago.tipo_pago',
                    'pago.estado',
                    'venta.fecha',
                    'pago.fecha as hora_pago'
                )
                ->orderByDesc('venta.id')
                ->get();

            // Formatear fechas con timezone correcto
            $ventasFormateadas = $ventasDiarias->map(function ($venta) use ($fechaCarbon) {
                $fechaVenta = Carbon::parse($venta->fecha)->setTimezone('America/La_Paz');
                $horaPago = Carbon::parse($venta->hora_pago)->setTimezone('America/La_Paz');

                return [
                    'nombre_cliente' => $venta->nombre_cliente,
                    'nombre_producto' => $venta->nombre_producto,
                    'precio' => (float)$venta->precio,
                    'cantidad' => $venta->cantidad,
                    'subtotal' => (float)$venta->subtotal,
                    'tipo_pago' => $venta->tipo_pago,
                    'estado' => $venta->estado,
                    'fecha' => $fechaVenta->format('d/m/Y'),
                    'hora' => $horaPago->format('H:i:s')
                ];
            });

            // Calcular total usando SUM de subtotales para mayor precisión
            $totalVentas = $ventasDiarias->sum('subtotal');

            // Obtener información detallada de fecha
            $fechaInfo = [
                'timezone' => 'America/La_Paz',
                'fecha_actual' => $fechaCarbon->format('d/m/Y'),
                'hora_actual' => $fechaCarbon->format('H:i:s'),
                'fecha_utc_inicio' => $startOfDay->toDateTimeString(),
                'fecha_utc_fin' => $endOfDay->toDateTimeString()
            ];

            return Inertia::render('Admin/VentasDiarias/Index', [
                'ventasDiarias' => $ventasFormateadas,
                'totalVentas' => $totalVentas,
                'fechaActual' => $fechaCarbon->toDateString(),
                'fechaInfo' => $fechaInfo,
                'horaActual' => $fechaCarbon->format('H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error en VentasDiariasController: ' . $e->getMessage());

            return Inertia::render('Admin/VentasDiarias/Index', [
                'ventasDiarias' => [],
                'totalVentas' => 0,
                'error' => 'Error al cargar datos: ' . $e->getMessage(),
                'fechaInfo' => [
                    'timezone' => 'America/La_Paz',
                    'error_time' => now()->format('H:i:s')
                ]
            ]);
        }
    }

    public function descargarReporte(Request $request)
    {
        $fechaReporte = $request->input('fecha', Carbon::now('America/La_Paz')->toDateString());

        // Conversión exacta a UTC
        $startOfDay = Carbon::parse($fechaReporte, 'America/La_Paz')->startOfDay()->setTimezone('UTC');
        $endOfDay = Carbon::parse($fechaReporte, 'America/La_Paz')->endOfDay()->setTimezone('UTC');

        try {
            // Consulta idéntica a la del índice
            $ventasDiarias = DB::table('venta')
                ->join('users', 'venta.id_cliente', '=', 'users.id')
                ->join('pago', 'venta.id_pago', '=', 'pago.id')
                ->join('detalle_venta', 'venta.id', '=', 'detalle_venta.id_venta')
                ->join('producto', 'detalle_venta.id_producto', '=', 'producto.id')
                ->whereBetween('venta.fecha', [$startOfDay, $endOfDay]) // Solo este filtro
                ->select(
                    'users.name as nombre_cliente',
                    'producto.nombre as nombre_producto',
                    'producto.precio',
                    'detalle_venta.cantidad',
                    DB::raw('producto.precio * detalle_venta.cantidad as subtotal'), // Mismo cálculo
                    'pago.tipo_pago',
                    'pago.estado',
                    'venta.fecha',
                    'pago.fecha as hora_pago' // Agregar hora pago
                )
                ->orderByDesc('venta.id') // Mismo orden
                ->get();

            // Formateo idéntico al índice
            $ventasFormateadas = $ventasDiarias->map(function ($venta) {
                $fechaVenta = Carbon::parse($venta->fecha)->setTimezone('America/La_Paz');

                return [
                    'nombre_cliente' => $venta->nombre_cliente,
                    'nombre_producto' => $venta->nombre_producto,
                    'precio' => number_format((float)$venta->precio, 2, '.', ''),
                    'cantidad' => $venta->cantidad,
                    'subtotal' => number_format((float)$venta->subtotal, 2, '.', ''),
                    'tipo_pago' => $venta->tipo_pago,
                    'monto' => number_format((float)$venta->subtotal, 2, '.', ''), // Usar subtotal calculado
                    'estado' => $venta->estado,
                    'fecha' => $fechaVenta->format('d/m/Y')
                ];
            });

            // Calcular total igual que en índice
            $totalVentas = $ventasDiarias->sum('subtotal');
            $totalVentasFormateado = number_format($totalVentas, 2, '.', '');

            // Generar el reporte en CSV
            $fechaFormateada = Carbon::parse($fechaReporte)->format('d-m-Y');
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="reporte_ventas_' . $fechaFormateada . '.csv"',
            ];

            $callback = function () use ($ventasFormateadas, $totalVentasFormateado, $fechaFormateada) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM para compatibilidad con Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Título del reporte (lo colocamos como primera fila)
                fputcsv($file, ['REPORTE DE VENTAS - ' . $fechaFormateada], ';');
                // Fila vacía
                fputcsv($file, [], ';');

                // Encabezados con nombres más descriptivos
                $encabezados = [
                    'CLIENTE',
                    'PRODUCTO',
                    'PRECIO UNITARIO (Bs)',
                    'CANTIDAD',
                    'SUBTOTAL (Bs)',
                    'MÉTODO DE PAGO',
                    'MONTO PAGADO (Bs)',
                    'ESTADO',
                    'FECHA'
                ];
                fputcsv($file, $encabezados, ';');

                // Datos formateados - cada fila corresponde a una venta
                foreach ($ventasFormateadas as $venta) {
                    $fila = [
                        $venta['nombre_cliente'],
                        $venta['nombre_producto'],
                        $venta['precio'],
                        $venta['cantidad'],
                        $venta['subtotal'],
                        $venta['tipo_pago'],
                        $venta['monto'],
                        $venta['estado'],
                        $venta['fecha']
                    ];
                    fputcsv($file, $fila, ';');
                }

                // Línea en blanco
                fputcsv($file, [], ';');

                // Totales con formato
                fputcsv($file, ['', '', '', '', 'TOTAL VENTAS:', $totalVentasFormateado . ' Bs'], ';');

                // Pie de página
                fputcsv($file, [], ';');
                fputcsv($file, ['Reporte generado el: ' . Carbon::now('America/La_Paz')->format('d/m/Y H:i:s')], ';');

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error en descargarReporte: ' . $e->getMessage());

            // En caso de error, devolver un archivo CSV con mensaje de error
            return response()->streamDownload(function () use ($e) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, ['Error al generar el reporte'], ';');
                fputcsv($file, [$e->getMessage()], ';');
                fclose($file);
            }, 'error_reporte.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
    }

    // Método de depuración para verificar datos
    public function testDatos()
    {
        try {
            $totalVentas = DB::table('venta')->count();
            $totalUsuarios = DB::table('users')->count();
            $totalProductos = DB::table('producto')->count();

            // Información específica para depurar el problema con las ventas
            $fechaActual = Carbon::now('America/La_Paz')->toDateString();
            $ventasHoy = DB::table('venta')
                ->whereRaw('DATE(venta.fecha) = ?', [$fechaActual])
                ->count();

            $algunasVentas = DB::table('venta')
                ->select('id', 'fecha', 'total', 'id_cliente', 'id_pago')
                ->orderBy('fecha', 'desc')
                ->limit(5)
                ->get();

            // Verificar si hay registros en detalle_venta
            $detallesVenta = DB::table('detalle_venta')
                ->join('venta', 'detalle_venta.id_venta', '=', 'venta.id')
                ->whereRaw('DATE(venta.fecha) = ?', [$fechaActual])
                ->count();

            return response()->json([
                'total_ventas' => $totalVentas,
                'total_usuarios' => $totalUsuarios,
                'total_productos' => $totalProductos,
                'ventas_hoy' => $ventasHoy,
                'detalles_venta_hoy' => $detallesVenta,
                'algunas_ventas' => $algunasVentas,
                'algunos_usuarios' => DB::table('users')->limit(3)->get(),
                'fecha_actual_sistema' => Carbon::now()->toDateTimeString(),
                'fecha_actual_bolivia' => Carbon::now('America/La_Paz')->toDateTimeString(),
                'fecha_consulta' => $fechaActual
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage(),
                'lugar' => 'testDatos'
            ], 500);
        }
    }

    // Método para limpiar las ventas después de descargar el reporte
    public function limpiarVentas(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::now('America/La_Paz')->toDateString());

        try {
            // Obtener el número de ventas a limpiar
            $numeroVentas = DB::table('venta')
                ->whereRaw('DATE(venta.fecha) = ?', [$fecha])
                ->count();

            // Solo retornamos una respuesta exitosa para que el frontend maneje la limpieza visual
            return redirect()->route('admin.ventaDiaria.index');
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'mensaje' => $e->getMessage()
            ], 500);
        }
    }
}
