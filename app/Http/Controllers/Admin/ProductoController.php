<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CounterHelper;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search'); // Variable para el buscador
        $count = CounterHelper::incrementCounter('producto');

        $productos = DB::table('producto')
            ->join('producto_almacen', 'producto.id', '=', 'producto_almacen.id_producto')
            ->join('categoria', 'producto.id_categoria', '=', 'categoria.id')
            ->join('almacen', 'producto_almacen.id_almacen', '=', 'almacen.id')
            ->leftJoin('promocion', 'producto.id_promocion', '=', 'promocion.id') // Join con la tabla promoción
            ->select(
                'producto.id',
                'producto.nombre as producto_nombre',
                'producto.descripcion',
                'producto.precio',
                'promocion.descuento as promocion_descuento',
                'producto.imagen',
                'categoria.nombre as categoria_nombre',
                'producto_almacen.stock as stock',
                'almacen.nombre as almacen_nombre',
                'producto.visible' // Añadir la columna visible
            )
            ->when($search, function ($query, $search) {
                $query->where('producto.nombre', 'like', "%$search%")
                    ->orWhere('producto.descripcion', 'like', "%$search%")
                    ->orWhere('categoria.nombre', 'like', "%$search%");
            })
            ->orderBy('producto.id', 'desc')
            ->get();

        // Obtener todas las promociones
        $promociones = DB::table('promocion')
            ->select('id', 'descuento')
            ->get();

        return Inertia::render('Admin/Productos/Index', [
            'productos' => $productos,
            'promociones' => $promociones,
            'count' => $count,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        // Validaciones
        $request->validate([
            'nombre' => 'required|string|max:255',
            'id_categoria' => 'required|exists:categoria,id',
            'descripcion' => 'nullable|string',
            'id_almacen' => 'required|exists:almacen,id',
            'id_promocion' => 'nullable|e                                                              xists:promocion,id',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'visible' => 'boolean',
        ]);

        $path = null;

        // Guardar la imagen en disco
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('images', 'public');
        }

        // Insertar el producto en la base de datos
        $productoId = DB::table('producto')->insertGetId([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_categoria' => $request->id_categoria,
            'id_promocion' => $request->id_promocion,
            'precio' => $request->precio,
            'imagen' => $path, // Guardar la ruta de la imagen
            'visible' => $request->visible ?? true, // Guardar el estado visible
        ]);

        // Insertar el stock inicial en producto_almacen
        DB::table('producto_almacen')->insert([
            'id_producto' => $productoId,
            'id_almacen' => $request->id_almacen,
            'stock' => $request->stock ?? 0, // Usar el stock enviado o 0 por defecto
        ]);

        return redirect()->route('admin.productos.index')->with('success', 'Producto creado con éxito.');
    }




    public function edit(Request $request, $id)
    {
        // Validaciones
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_categoria' => 'required|exists:categoria,id',
            'id_almacen' => 'required|exists:almacen,id',
            'id_promocion' => 'nullable|exists:promocion,id',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'stock' => 'required|numeric|min:0',
            'visible' => 'boolean', // Nueva validación

        ]);

        // Obtener el producto actual
        $producto = DB::table('producto')->where('id', $id)->first();
        if (!$producto) {
            return redirect()->route('admin.productos.index')->with('error', 'Producto no encontrado.');
        }

        $productoData = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_categoria' => $request->id_categoria,
            'id_promocion' => $request->id_promocion,
            'precio' => $request->precio,
            'visible' => $request->visible ?? true, // Actualizar el estado visible
        ];

        // Manejo de la imagen
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('images', 'public');

            // Actualiza la ruta de la imagen en el array
            $productoData['imagen'] = $path;

            // Elimina la imagen anterior si existe
            if ($producto->imagen && Storage::exists('public/' . $producto->imagen)) {
                Storage::delete('public/' . $producto->imagen);
            }
        }

        // Actualizar el producto en la base de datos
        DB::table('producto')->where('id', $id)->update($productoData);

        // Actualizar el stock en la tabla producto_almacen
        $productoAlmacen = DB::table('producto_almacen')
            ->where('id_producto', $id)
            ->where('id_almacen', $request->id_almacen)
            ->first();

        if ($productoAlmacen) {
            // Si ya existe una relación entre producto y almacén, actualiza el stock
            DB::table('producto_almacen')
                ->where('id_producto', $id)
                ->where('id_almacen', $request->id_almacen)
                ->update(['stock' => $request->stock]);
        } else {
            // Si no existe, crea una nueva relación
            DB::table('producto_almacen')->insert([
                'id_producto' => $id,
                'id_almacen' => $request->id_almacen,
                'stock' => $request->stock,
            ]);
        }

        return redirect()->route('admin.productos.index')->with('success', 'Producto actualizado correctamente.');
    }




    public function upload(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $path = $request->file('foto')->store('images', 'public'); // Guarda en el disco público
        $relativePath = str_replace('public/', '', $path); // Ruta relativa para usar en el frontend

        return response()->json(['uploadedPath' => $relativePath])->header('X-Inertia', true);
    }



    public function destroy($id)
    {
        // Obtener el producto
        $producto = DB::table('producto')->where('id', $id)->first();

        if (!$producto) {
            return redirect()->route('admin.productos.index')->with('error', 'Producto no encontrado');
        }

        // Eliminar la imagen si existe
        if ($producto->imagen && Storage::exists('public/' . $producto->imagen)) {
            Storage::delete('public/' . $producto->imagen);
        }

        // Eliminar relaciones en producto_almacen
        DB::table('producto_almacen')->where('id_producto', $id)->delete();

        // Eliminar el producto
        DB::table('producto')->where('id', $id)->delete();

        return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado con éxito');
    }
}
