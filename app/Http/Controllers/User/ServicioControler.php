<?php

namespace App\Http\Controllers\User;

use App\Helpers\CounterHelper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ServicioControler extends Controller
{
    public function index(Request $request)
    {
        $roles = ['cliente', 'administrador'];
        $permissions = ['vista_cliente'];
        if (Gate::denies('check-access', [$roles, $permissions])) {
            return redirect()->route('login');
        }
        $search = $request->input('search'); //variable del buscador
        $servicios = DB::table('servicio')
            ->select('servicio.*')
            ->when($search, function ($query, $search) {
                $query->where('servicio.nombre', 'like', "%$search%")
                    ->orWhere('servicio.descripcion', 'like', "%$search%");
            })
            ->orderBy('servicio.id', 'desc')
            ->get()
            ->map(function ($servicios) {
                $servicios->imagen = url('storage/' . $servicios->imagen); // Ajusta la ruta
                return $servicios;
            });
        $count = CounterHelper::incrementCounter('servicios');
        //dd($servicios);
        return Inertia::render('User/Servicio', [ //Redirige a la vista de la tienda para hacer compras
            //'auth' => Auth::user(),
            'search' => $search,
            'servicios' => $servicios,
            'count' => $count
        ]);
    }

    public function ordenServicio()
    {
        // Obtener usuario autenticado
        $user = Auth::user();

        // Obtener los servicios del carrito desde la sesión
        $carrito = session()->get('carrito', []);
        $count = CounterHelper::incrementCounter('orden-servicio');
        return Inertia::render('User/OrdenServicio', [
            'auth' => ['user' => $user],
            'carrito' => $carrito, // Pasamos el carrito a la vista
            'count' => $count
        ]);
    }


    public function storeOrdenServicio(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|integer',
            'servicios' => 'required|array|min:1',
            'monto_total' => 'required|numeric|min:0',
            'fecha' => 'required|date',
        ]);

        foreach ($request->servicios as $servicio) {
            DB::table('orden_servicio')->insert([
                'id_cliente' => $request->id_cliente,
                'id_servicio' => $servicio['id_servicio'],
                'monto_total' => $request->monto_total,
                'fecha' => $request->fecha,
            ]);
        }

        return redirect()->route('servicio.index')->with('success', 'Orden de servicio creada con éxito.');
    }
}
