<?php

namespace App\Http\Controllers\User;

use App\Helpers\CounterHelper;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserControler extends Controller
{
    public function index(Request $request)
    {
        $roles = ['cliente', 'administrador'];
        $permissions = ['vista_cliente'];
        if (Gate::denies('check-access', [$roles, $permissions])) {
            return redirect()->route('login');
        }
        $search = $request->input('search'); //variable del buscador
        $productos = DB::table('producto')
            ->join('promocion', 'producto.id_promocion', '=', 'promocion.id')
            ->join('categoria', 'producto.id_categoria', '=', 'categoria.id')
            ->leftJoin('producto_almacen', 'producto.id', '=', 'producto_almacen.id_producto')
            ->select(
                'producto.*',
                'promocion.descuento as productos_descuentos',
                'producto_almacen.stock',
                'categoria.nombre as categoria_nombre' //
            )
            ->where('producto_almacen.stock', '>', 0)
            ->where('producto.visible', true) // Añadir este filtro
            ->when($search, function ($query, $search) {
                $query->where('producto.nombre', 'like', "%$search%")
                    ->orWhere('producto.descripcion', 'like', "%$search%");
            })
            ->orderBy('producto.id', 'desc')
            ->get()
            ->map(function ($producto) {
                $producto->imagen = url('storage/' . $producto->imagen); // Ajusta la ruta
                return $producto;
            });

        $count = CounterHelper::incrementCounter('/');
        $promociones = DB::table('promocion')->get();
        //dd($productos);
        return Inertia::render('User/Index', [ //Redirige a la vista de la tienda para hacer compras
            //'auth' => Auth::user(),
            'canLogin' => app('router')->has('login'),
            'canRegister' => app('router')->has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'search' => $search,
            'productos' => $productos,
            'count' => $count
        ]);
    }

    public function store(Request $request)
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes estar autenticado para realizar una compra');
        }

        // Guardar el carrito pendiente
        DB::table('carritos_pendientes')->insert([
            'id_users' => Auth::id(),
            'productos' => json_encode($request->productos), // Guardar como JSON
        ]);

        // Redirigir al carrito para realizar el pago
        return redirect()->route('carro.pago')->with('success', 'Carrito guardado, procede a realizar el pago.');
    }
}
