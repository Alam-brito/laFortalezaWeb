<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class QrControler extends Controller
{
    public function qr(Request $request)
    {
        try {
            // Parámetros necesarios para generar el QR
            $lcComerceID = "d029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c";
            $lnMoneda = 2;
            $lnTelefono = $request->telefono;
            $lcNombreUsuario = $request->razon_social;
            $lnCiNit = $request->nit;
            $lcNroPago = "test-grupo06sa" . rand(100000, 999999);
            $lnMontoClienteEmpresa = $request->total;
            $lcCorreo = $request->correo;
            //$lcUrlCallBack = route('pago.callback');
            $lcUrlCallBack = "https://93db356b-c04b-4bc6-a122-5280401ec5eb-00-1qwa8jkndizo.picard.replit.dev/api/urlcallback";

            $lcUrlReturn = "http://localhost:8000/";

            $laPedidoDetalle = [
                [
                    'Serial' => '123',
                    'Producto' => 'Producto ejemplo',
                    'Cantidad' => 1,
                    'Precio' => $lnMontoClienteEmpresa,
                    'Descuento' => 0,
                    'Total' => $lnMontoClienteEmpresa,
                ]
            ];

            // API para generar QR
            $lcUrl = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/generarqrv2";
            $laHeader = ['Accept' => 'application/json'];
            $laBody = [
                "tcCommerceID" => $lcComerceID,
                "tnMoneda" => $lnMoneda,
                "tnTelefono" => $lnTelefono,
                'tcNombreUsuario' => $lcNombreUsuario,
                'tnCiNit' => $lnCiNit,
                'tcNroPago' => $lcNroPago,
                "tnMontoClienteEmpresa" => $lnMontoClienteEmpresa,
                "tcCorreo" => $lcCorreo,
                'tcUrlCallBack' => $lcUrlCallBack,
                "tcUrlReturn" => $lcUrlReturn,
                'taPedidoDetalle' => $laPedidoDetalle
            ];

            $loClient = new Client();
            $loResponse = $loClient->post($lcUrl, [
                'headers' => $laHeader,
                'json' => $laBody
            ]);

            $laResult = json_decode($loResponse->getBody()->getContents());
            $laValues = explode(";", $laResult->values)[1];
            $laQrImage = json_decode($laValues)->qrImage;

            // Respuesta JSON para AJAX
            return response()->json([
                'success' => true,
                'qrImage' => $laQrImage,
                'nroPago' => $lcNroPago // Devuelve el número de pago
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            // Validar los datos del carrito enviados desde el frontend
            $carrito = $request->input('carrito');
            $total = $request->input('total');
            $user = $request->input('user');

            // Verificar que el carrito no esté vacío
            if (empty($carrito)) {
                return response()->json(['success' => false, 'message' => 'El carrito está vacío.']);
            }

            // Validar que el usuario y el total sean válidos
            if (empty($user['id']) || empty($total)) {
                return response()->json(['success' => false, 'message' => 'Datos de usuario o total no válidos.']);
            }

            // Inicia una transacción para garantizar la consistencia
            DB::beginTransaction();

            // Registrar el pago en la tabla `pago`
            $pagoId = DB::table('pago')->insertGetId([
                'tipo_pago' => 'QR',
                'monto' => $total,
                'estado' => 'completado',
                'fecha' => now(),
            ]);

            // Registrar la venta en la tabla `venta`
            $ventaId = DB::table('venta')->insertGetId([
                'total' => $total,
                'fecha' => now(),
                'id_cliente' => $user['id'], // ID del cliente autenticado
                'id_pago' => $pagoId,
            ]);

            // Registrar los detalles de la venta y actualizar el stock
            foreach ($carrito as $producto) {
                // Validar que los datos del producto estén completos
                if (
                    empty($producto['id']) ||
                    empty($producto['id_almacen']) ||
                    empty($producto['cantidad']) ||
                    empty($producto['subtotal'])
                ) {
                    throw new \Exception('Datos del producto incompletos.');
                }

                // Verificar stock disponible
                $stockActual = DB::table('producto_almacen')
                    ->where('id_producto', $producto['id'])
                    ->where('id_almacen', $producto['id_almacen'])
                    ->value('stock');

                if ($stockActual === null) {
                    throw new \Exception("El producto ID {$producto['id']} no existe en el almacén ID {$producto['id_almacen']}.");
                }

                if ($stockActual < $producto['cantidad']) {
                    throw new \Exception("Stock insuficiente para el producto ID {$producto['id']} en el almacén ID {$producto['id_almacen']}.");
                }

                // Registrar el detalle de la venta
                DB::table('detalle_venta')->insert([
                    'cantidad' => $producto['cantidad'],
                    'id_producto' => $producto['id'],
                    'id_almacen' => $producto['id_almacen'],
                    'id_venta' => $ventaId,
                ]);

                // Actualizar el stock en el almacén
                DB::table('producto_almacen')
                    ->where('id_producto', $producto['id'])
                    ->where('id_almacen', $producto['id_almacen'])
                    ->decrement('stock', $producto['cantidad']);
            }

            // Eliminar el carrito pendiente después de procesar la venta
            DB::table('carritos_pendientes')
                ->where('id_users', $user['id'])
                ->delete();

            // Confirmar la transacción
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Pago confirmado y compra registrada.']);
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
