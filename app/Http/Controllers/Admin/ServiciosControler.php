<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CounterHelper;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiciosControler extends Controller
{
    public function index()
    {
        // Obtener servicios con datos asociados
        $servicios = DB::table('servicio')
            ->select(
                'servicio.id',
                'servicio.nombre',
                'servicio.descripcion',
                'servicio.precio',
                'servicio.imagen'
            )
            ->orderBy('servicio.id', 'desc')
            ->get();
            $count = CounterHelper::incrementCounter('servicios');
        return Inertia::render('Admin/Servicios/Index', [
            'servicios' => $servicios,
            'count' => $count
        ]);
    }

    public function store(Request $request)
    {
        // Validar datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Nueva validación de imagen
        ]);

        // Guardar imagen
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('images', 'public');
        } else {
            $path = null;
        }

        // Insertar nuevo servicio
        DB::table('servicio')->insert([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $path, // Guardar la ruta de la imagen
        ]);

        return redirect()->route('admin.servicios.index')->with('success', 'Servicio creado con éxito');
    }

    public function edit(Request $request, $id)
    {
        // Validar datos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Nueva validación de imagen
        ]);

        // Obtener el servicio
        $servicio = DB::table('servicio')->where('id', $id)->first();

        // Manejo de imagen
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($servicio->imagen) {
                Storage::disk('public')->delete($servicio->imagen);
            }
            // Guardar la nueva imagen
            $path = $request->file('imagen')->store('servicios', 'public');
        } else {
            $path = $servicio->imagen;
        }

        // Actualizar servicio
        DB::table('servicio')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'imagen' => $path,
        ]);

        return redirect()->route('admin.servicios.index')->with('success', 'Servicio actualizado con éxito');
    }

    public function destroy($id)
    {
        // Obtener el servicio
        $servicio = DB::table('servicio')->where('id', $id)->first();

        // Eliminar imagen si existe
        if ($servicio->imagen) {
            Storage::disk('public')->delete($servicio->imagen);
        }

        // Eliminar servicio
        DB::table('servicio')->where('id', $id)->delete();

        return redirect()->route('admin.servicios.index')->with('success', 'Servicio eliminado con éxito');
    }
}
