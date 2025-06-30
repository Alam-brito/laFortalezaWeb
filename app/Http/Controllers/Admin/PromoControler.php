<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Helpers\CounterHelper;
use Illuminate\Support\Facades\DB;

class PromoControler extends Controller
{
    public function index()
    {
        $count = CounterHelper::incrementCounter('promo');
        $promociones = DB::table('promocion')
            ->orderBy('promocion.id', 'desc')
            ->get();
        return Inertia::render('Admin/Promociones/Index', [
            'promociones' => $promociones,
            'count' => $count
        ]);
    }

    public function store(Request $request)
    {
        DB::table('promocion')->insert([
            'descuento' => $request->descuento,
            'fecha_final' => $request->fecha_final,
            'fecha_inicio' => now(),
        ]);
        return redirect()->route('admin.promo.index')->with('success', 'Promocion creada con exito');
    }

    public function destroy($id)
    {
        // Eliminar el producto de la base de datos
        DB::table('promocion')->where('id', $id)->delete();
        return redirect()->route('admin.promo.index')->with('success', 'Promo eliminada con éxito');
    }
}
