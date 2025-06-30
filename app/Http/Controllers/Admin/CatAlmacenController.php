<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CounterHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CatAlmacenController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categoria')->select('id', 'nombre', 'descripcion')->orderBy('id', 'desc')->get();
        $almacenes = DB::table('almacen')->select('id', 'nombre', 'ubicacion')->orderBy('id', 'desc')->get();
        $count = CounterHelper::incrementCounter('usuarios');
        return Inertia::render('Admin/CatAlmacen/Index', [
            'categorias' => $categorias,
            'almacenes' => $almacenes,
            'count' => $count
        ]);
    }

    public function storeCategoria(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
        ]);

        DB::table('categoria')->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('almacen.categoria.index')->with('success', 'Categoría creada con éxito.');
    }


    public function storeAlmacen(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
        ]);

        DB::table('almacen')->insert([
            'nombre' => $request->nombre,
            'ubicacion' => $request->ubicacion,
        ]);

        return redirect()->route('almacen.categoria.index')->with('success', 'Almacén creado con éxito.');
    }

    public function updateCategoria(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
        ]);

        DB::table('categoria')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('almacen.categoria.index')->with('success', 'Categoría actualizada con éxito.');
    }

    public function updateAlmacen(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
        ]);

        DB::table('almacen')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'ubicacion' => $request->ubicacion,
        ]);

        return redirect()->route('almacen.categoria.index')->with('success', 'Almacén actualizado con éxito.');
    }

    public function destroyCategoria($id)
    {
        DB::table('categoria')->where('id', $id)->delete();
        return redirect()->route('almacen.categoria.index')->with('success', 'Categoría eliminada con éxito.');
    }

    public function destroyAlmacen($id)
    {
        DB::table('almacen')->where('id', $id)->delete();
        return redirect()->route('almacen.categoria.index')->with('success', 'Almacén eliminado con éxito.');
    }
}
