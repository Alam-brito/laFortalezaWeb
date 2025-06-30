<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProfileController;
use App\Helpers\CounterHelper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

//Clientes Controler
//Route::get('/', [\App\Http\Controllers\User\UserControler::class, 'index'])->name('user.home');


Route::middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\User\UserControler::class, 'index'])->name('user.home'); //
    // Otras rutas de usuario estándar
    Route::post('/compra', [\App\Http\Controllers\User\UserControler::class, 'store'])->name('user.compra');
    Route::post('/qr', [\App\Http\Controllers\User\QrControler::class, 'qr'])->name('generar_qr');

    //Edicion del perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); //
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Añadir al carro
    Route::get('/pago', [\App\Http\Controllers\User\CarControler::class, 'index'])->name('carro.pago'); //
    Route::post('/pago/chek', [\App\Http\Controllers\User\CarControler::class, 'confirmarPago'])->name('carro.confirmarPago'); //
    Route::get('/factura', [\App\Http\Controllers\User\CarControler::class, 'Factura'])->name('carro.factura'); //

    Route::post('/pago/callback', [\App\Http\Controllers\User\QrControler::class, 'handleCallback'])->name('pago.callback');
    Route::get('/verificar-pago/{nroPago}', [\App\Http\Controllers\User\QrControler::class, 'verificarPago'])->name('pago.verificar');

    //Ruta de los servicios
    Route::get('/servicios', [\App\Http\Controllers\User\ServicioControler::class, 'index'])->name('servicio.index'); //
    Route::get('/orden-servicio', [\App\Http\Controllers\User\ServicioControler::class, 'ordenServicio'])->name('user.orden_servicio'); //
    Route::post('/orden-servicio/store', [\App\Http\Controllers\User\ServicioControler::class, 'storeOrdenServicio'])->name('user.orden_servicio.store');
});


//Route::get('/dashboard', [\App\Http\Controllers\User\DashboardControler::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

//Route::post('/qr', [\App\Http\Controllers\User\QrControler::class, 'qr'])->name('generar_qr');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); //
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Route::get('/dashboard', [Gate::authorize(), 'index'])->name('admin.dashboard');

    //Route::post('/user/compra', [\App\Http\Controllers\User\UserControler::class, 'store'])->name('user.compra');
    //Route::post('/qr', [\App\Http\Controllers\User\QrControler::class, 'qr'])->name('generar_qr');

    //Añadir al carro
    //Route::get('/pago', [\App\Http\Controllers\User\CarControler::class, 'index'])->name('carro.pago');//
});

//fin

Route::post('/roles/assign-permission', [\App\Http\Controllers\Auth\RoleControler::class, 'assignPermissionToRole'])
    ->middleware('permission:assign_permissions');


Route::post('/check-user-role', [\App\Http\Controllers\Auth\RoleControler::class, 'checkUserRole'])
    ->name('check.user.role');



//Ruta de administradores
Route::group(['prefix' => 'admin', 'middleware' => 'redirectAdmin'], function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login'); //
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard'); //

    //Ruta de los productos
    Route::get('/productos', [\App\Http\Controllers\Admin\ProductoController::class, 'index'])->name('admin.productos.index'); //
    Route::post('/productos/crear', [\App\Http\Controllers\Admin\ProductoController::class, 'store'])->name('admin.productos.store');
    Route::put('/productos/update/{id}', [\App\Http\Controllers\Admin\ProductoController::class, 'update'])->name('admin.productos.update');
    Route::post('/productos/edit/{id}', [\App\Http\Controllers\Admin\ProductoController::class, 'edit'])->name('admin.productos.edit');
    Route::delete('/productos/eliminar/{id}', [\App\Http\Controllers\Admin\ProductoController::class, 'destroy'])->name('admin.productos.delete');
    // Route::post('/productos/upload', [\App\Http\Controllers\Admin\ProductoController::class, 'upload'])->name('admin.productos.upload');

    //Ruta control de usuarios
    Route::get('/usuarios', [\App\Http\Controllers\Admin\UserControler::class, 'index'])->name('admin.usuarios.index'); //
    Route::put('/usuarios/update/{id}', [\App\Http\Controllers\Admin\UserControler::class, 'update'])->name('admin.usuarios.update');
    Route::delete('/usuarios/destroy/{id}', [\App\Http\Controllers\Admin\UserControler::class, 'destroy'])->name('admin.usuarios.delete');

    //Rutas de los roles y permisos
    Route::post('/roles', [\App\Http\Controllers\Admin\UserControler::class, 'storeRol'])->name('admin.roles.store');
    Route::delete('/roles/{id}', [\App\Http\Controllers\Admin\UserControler::class, 'destroyRol'])->name('admin.roles.delete');

    Route::post('/permisos', [\App\Http\Controllers\Admin\UserControler::class, 'storePermiso'])->name('admin.permisos.store');
    Route::delete('/permisos/{id}', [\App\Http\Controllers\Admin\UserControler::class, 'destroyPermiso'])->name('admin.permisos.delete');
    Route::delete('/admin/usuarios/{userId}/remover-rol/{rolId}', [\App\Http\Controllers\Admin\UserControler::class, 'removeRole'])->name('admin.usuarios.removerRol');

    //fin

    //Ruta de las promociones
    Route::get('/promo', [\App\Http\Controllers\Admin\PromoControler::class, 'index'])->name('admin.promo.index'); //
    Route::post('/promo/crear', [\App\Http\Controllers\Admin\PromoControler::class, 'store'])->name('admin.promo.crear');
    Route::delete('/promo/eliminar/{id}', [\App\Http\Controllers\Admin\PromoControler::class, 'destroy'])->name('admin.promo.delete');
    //fin

    //Ruta de los servicios
    Route::get('/servicios', [\App\Http\Controllers\Admin\ServiciosControler::class, 'index'])->name('admin.servicios.index'); //
    Route::post('/servicios/crear', [\App\Http\Controllers\Admin\ServiciosControler::class, 'store'])->name('admin.servicios.crear');
    Route::put('/servicios/update/{id}', [\App\Http\Controllers\Admin\ServiciosControler::class, 'edit'])->name('admin.servicios.update');
    Route::delete('/servicios/eliminar/{id}', [\App\Http\Controllers\Admin\ServiciosControler::class, 'destroy'])->name('admin.servicios.delete');
    //fin

    //Rutas de inventarios
    Route::get('/inventarios', [\App\Http\Controllers\Admin\InventarioController::class, 'index'])->name('admin.inventarios.index'); //
    Route::get('/inventarios/crear', [\App\Http\Controllers\Admin\InventarioCrearController::class, 'index'])->name('admin.inventarios.crear'); //

    /*Route::get('/inventarios/crear', function () {
        $count = CounterHelper::incrementCounter('inventarios/crear');
        return Inertia::render('Admin/Inventarios/IndexCrear', ['count' => $count]);
    })->name('admin.inventarios.crear');*/

    Route::post('/inventarios/store', [\App\Http\Controllers\Admin\InventarioController::class, 'store'])->name('admin.inventarios.store');
    Route::delete('/inventario/borrar/{id}', [App\Http\Controllers\Admin\InventarioController::class, 'destroy'])->name('admin.inventario.delete');
    Route::post('/inventarios/ajustar', [\App\Http\Controllers\Admin\InventarioController::class, 'ajustarInventario'])->name('admin.inventarios.ajustar');

    // Rutas para obtener datos dinámicos
    Route::get('/ruta/categorias', [\App\Http\Controllers\Admin\RutasController::class, 'getCategorias'])->name('ruta.categorias');
    Route::get('/ruta/productos/{categoriaId}', [\App\Http\Controllers\Admin\RutasController::class, 'getProductosByCategoria'])->name('ruta.productos');
    Route::get('/ruta/almacenes', [\App\Http\Controllers\Admin\RutasController::class, 'getAlmacenes'])->name('ruta.almacenes');
    Route::post('/calcular-stock', [\App\Http\Controllers\Admin\RutasController::class, 'calculateStock'])->name('ruta.calcular-stock');

    //Ruta categoría y almacenes
    Route::get('/catAlmacen', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'index'])->name('almacen.categoria.index');

    // Rutas para Categorías
    Route::post('/categoria/store', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'storeCategoria'])->name('almacen.categoria.store');
    Route::put('/categoria/update/{id}', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'updateCategoria'])->name('almacen.categoria.update');
    Route::delete('/categoria/destroy/{id}', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'destroyCategoria'])->name('almacen.categoria.destroy');

    // Rutas para Almacenes
    Route::post('/almacen/store', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'storeAlmacen'])->name('almacen.almacen.store');
    Route::put('/almacen/update/{id}', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'updateAlmacen'])->name('almacen.almacen.update');
    Route::delete('/almacen/destroy/{id}', [\App\Http\Controllers\Admin\CatAlmacenController::class, 'destroyAlmacen'])->name('almacen.almacen.destroy');

    // Rutas para Reportes de Ventas
    Route::get('/reportes', [\App\Http\Controllers\Admin\ReporteControler::class, 'index'])->name('index.reporte');
    Route::post('/buscar_ventas', [\App\Http\Controllers\Admin\ReporteControler::class, 'buscarVentas'])->name('buscar.ventas');
    Route::get('/obtener_usuarios', function () {
        return response()->json(DB::table('users')->select('id', 'name')->get());
    })->name('obtener.usuarios');
    Route::post('/enviar_correo', [\App\Http\Controllers\Admin\ReporteControler::class, 'enviarCorreo'])->name('enviar.correo');

    //Ruta de reportes de inventarios
    // Nueva ruta para obtener productos
    Route::get('/obtener_productos', function () {
        return response()->json(DB::table('producto')->select('id', 'nombre')->get());
    })->name('obtener.productos');

    // Nueva ruta para obtener almacenes
    Route::get('/obtener_almacenes', function () {
        return response()->json(DB::table('almacen')->select('id', 'nombre')->get());
    })->name('obtener.almacenes');

    // Ruta para buscar movimientos de productos (ingresos y egresos)
    Route::post('/buscar_movimientos', [\App\Http\Controllers\Admin\ReporteControler::class, 'buscarMovimientos'])->name('buscar.movimientos');
    //Fin


    //Ruta de la tienda local
    // Vista principal de venta local
    Route::get('/ventaLocal', [\App\Http\Controllers\Admin\VentasController::class, 'index'])->name('admin.venta.index');

    // Búsqueda de productos
    Route::get('/ventaLocal/buscar', [\App\Http\Controllers\Admin\VentasController::class, 'buscarProductos'])->name('admin.venta.buscar');

    // Procesar venta
    Route::post('/ventaLocal/procesar', [\App\Http\Controllers\Admin\VentasController::class, 'procesarVenta'])->name('admin.venta.procesar');

    // Obtener factura
    Route::get('/ventaLocal/factura/{id}', [\App\Http\Controllers\Admin\VentasController::class, 'obtenerFactura'])->name('admin.venta.factura');
    //fin

    // Ruta para ver reportes de las ventas diarias
    Route::get('/ventaDiaria', [\App\Http\Controllers\Admin\VentasDiariasController::class, 'index'])->name('admin.ventaDiaria.index');

    // Ruta para descargar el reporte de ventas diarias
    Route::get('/ventaDiaria/descargar', [\App\Http\Controllers\Admin\VentasDiariasController::class, 'descargarReporte'])->name('admin.ventaDiaria.descargar');

    // Ruta para limpiar las ventas después de descargar el reporte
    Route::post('/ventaDiaria/limpiar', [\App\Http\Controllers\Admin\VentasDiariasController::class, 'limpiarVentas'])->name('admin.ventaDiaria.limpiar');

    // Ruta para probar la conexión y ver datos básicos (depuración)
    Route::get('/ventaDiaria/test', [\App\Http\Controllers\Admin\VentasDiariasController::class, 'testDatos'])->name('admin.ventaDiaria.test');
});

//fin

require __DIR__ . '/auth.php';
