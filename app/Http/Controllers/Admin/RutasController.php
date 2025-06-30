<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RutasController extends Controller
{
    public function getCategorias()
    {
        // Retorna todas las categorías
        $categorias = DB::table('categoria')->select('id', 'nombre')->get();
        return response()->json($categorias);
    }

    public function getProductosByCategoria($categoriaId)
    {
        // Retorna los productos filtrados por categoría
        $productos = DB::table('producto')
            ->where('id_categoria', $categoriaId)
            ->select('id', 'nombre')
            ->get();
        return response()->json($productos);
    }

    public function getAlmacenes()
    {
        // Retorna todos los almacenes disponibles
        $almacenes = DB::table('almacen')->select('id', 'nombre')->get();
        return response()->json($almacenes);
    }

    public function calculateStock(Request $request)
    {
        $request->validate([
            'id_categoria' => 'required|exists:categoria,id',
            'id_almacen' => 'required|exists:almacen,id',
        ]);

        // Obtener el stock actual basado en la categoría y almacén
        $stock = DB::table('producto_almacen')
            ->join('producto', 'producto_almacen.id_producto', '=', 'producto.id')
            ->where('producto.id_categoria', $request->id_categoria)
            ->where('producto_almacen.id_almacen', $request->id_almacen)
            ->value('producto_almacen.stock'); // Obtiene un solo valor del stock

        // Si no hay stock, devuelve 0
        return response()->json(['stock' => $stock ?? 0]);
    }
}
