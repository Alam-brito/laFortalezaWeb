<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Helpers\CounterHelper;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Auth;

class ReporteControler extends Controller
{
    public function index()
    {
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
        $count = CounterHelper::incrementCounter('reportes');
        return Inertia::render('Admin/Reportes/Index', [
            //'servicios' => $servicios,
            'count' => $count,
            'userRoles' => $roles,
            'userPermissions' => $permisos,
        ]);
    }

    public function buscarVentas(Request $request)
    {
        // Validar fechas
        $request->validate([
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        // Obtener ventas filtradas por rango de fechas y usuario
        $query = DB::table('venta')
            ->join('cliente', 'venta.id_cliente', '=', 'cliente.id')
            ->join('users', 'cliente.id', '=', 'users.id')
            ->join('pago', 'venta.id_pago', '=', 'pago.id')
            ->select(
                'venta.id',
                'users.name as nombre',
                'users.email as email',
                'venta.fecha',
                DB::raw('CAST(venta.total AS DECIMAL(10,2)) as total'),
                'pago.tipo_pago'
            )
            ->whereBetween('venta.fecha', [$request->fechaInicio, $request->fechaFin]);

        // Si el usuario no es "todos", filtrar por usuario
        if ($request->id_user !== "todos") {
            $query->where('users.id', $request->id_user);
        }

        $ventas = $query->orderBy('venta.fecha', 'desc')->get();

        return response()->json($ventas);
    }

    
    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'backup.2.0.2.5.1997@gmail.com'; // Reemplaza con tu email
            $mail->Password = 'ghnocaliywzbazpv'; // Reemplaza con tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->setFrom('backup.2.0.2.5.1997@gmail.com', 'TECNOWEB-VERANO');

            $destinatarios = explode(',', $request->to);
            foreach ($destinatarios as $email) {
                $mail->addAddress(trim($email));
            }

            $mail->Subject = $request->subject;
            $mail->Body = nl2br($request->message);
            $mail->isHTML(true);

            if ($mail->send()) {
                return response()->json(['message' => 'Correo enviado correctamente.'], 200);
            } else {
                return response()->json(['error' => 'Error al enviar el correo.'], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Excepción: ' . $e->getMessage()], 500);
        }
    }

    /*
    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'mail.tecnoweb.org.bo'; // Servidor SMTP de tecnoweb.org.bo
            $mail->SMTPAuth = true;
            $mail->Username = 'grupo06sa@tecnoweb.org.bo'; // Tu nuevo correo
            $mail->Password = 'grup006grup006*'; // Contraseña del correo
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Asegurar el cifrado correcto
            $mail->Port = 587; // Puerto recomendado para SMTP con STARTTLS

            $mail->setFrom('grupo06sa@tecnoweb.org.bo', 'Grupo 06 SA');

            // Agregar destinatarios
            $destinatarios = explode(',', $request->to);
            foreach ($destinatarios as $email) {
                $mail->addAddress(trim($email));
            }

            $mail->Subject = $request->subject;
            $mail->Body = nl2br($request->message);
            $mail->isHTML(true);

            if ($mail->send()) {
                return response()->json(['message' => 'Correo enviado correctamente.'], 200);
            } else {
                return response()->json(['error' => 'Error al enviar el correo.'], 500);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Excepción: ' . $e->getMessage()], 500);
        }
    }*/


    public function buscarMovimientos(Request $request)
    {
        // Construcción de la consulta
        $query = DB::table('detalle_ajuste')
            ->join('ajuste_inventario', 'detalle_ajuste.id_ajuste_inventario', '=', 'ajuste_inventario.id')
            ->join('producto', 'detalle_ajuste.id_producto', '=', 'producto.id')
            ->join('almacen', 'detalle_ajuste.id_almacen', '=', 'almacen.id')
            ->join('users', 'ajuste_inventario.id_user', '=', 'users.id')
            ->select(
                'detalle_ajuste.id_producto',
                'producto.nombre as producto',
                'detalle_ajuste.id_almacen',
                'almacen.nombre as almacen',
                'ajuste_inventario.fecha',
                'ajuste_inventario.glosa', // Se añade la glosa
                'ajuste_inventario.tipo',  // Ingreso o Egreso
                'detalle_ajuste.cantidad',
                'users.name as usuario'
            );

        // Si se proporcionan fechas, filtrar por ellas
        if (!empty($request->fechaInicio) && !empty($request->fechaFin)) {
            $query->whereBetween('ajuste_inventario.fecha', [$request->fechaInicio, $request->fechaFin]);
        }

        // Filtrar por producto si se seleccionó uno específico
        if ($request->id_producto !== "todos") {
            $query->where('detalle_ajuste.id_producto', $request->id_producto);
        }

        // Filtrar por almacén si se seleccionó uno específico
        if ($request->id_almacen !== "todos") {
            $query->where('detalle_ajuste.id_almacen', $request->id_almacen);
        }

        // Filtrar por tipo de movimiento (Ingreso/Egreso)
        if ($request->tipo_movimiento !== "todos") {
            $query->where('ajuste_inventario.tipo', $request->tipo_movimiento);
        }

        // Ejecutar la consulta
        $movimientos = $query->orderBy('ajuste_inventario.fecha', 'desc')->get();

        return response()->json($movimientos);
    }
}
