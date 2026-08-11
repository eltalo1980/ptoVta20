<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ConsultaPrecioController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPackController;
use App\Http\Controllers\StockPackDetalleController;
use App\Http\Controllers\StockPendienteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\ResumenVentaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\CierreDiaController;
use App\Http\Controllers\DevolucionVentaController;
use App\Http\Controllers\VentaAnalisisController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});
//Route::get('/', function () {    return view('welcome');});


Auth::routes();
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
//Route::get('/home', 'HomeController@index')->name('home');
// ok Route::get('/home', 'VentaController@index')->name('home');
Route::get('/home', 'VentaController@index')->name('home'); // cuando entro llego a esta pagina


///aca el controlador de ventas
Route::resource('Caja', 'CajaController');
Route::post('venta/park', 'CajaController@park')->name('venta.park');
Route::resource('venta', VentaController::class);
Route::post('ConsultaPrecio/clear', [ConsultaPrecioController::class, 'clear'])->name('ConsultaPrecio.clear');
Route::resource('ConsultaPrecio', ConsultaPrecioController::class);
Route::resource('pago', PagoController::class);
Route::resource('stock', StockController::class);
Route::resource('StockPack', StockPackController::class);
Route::resource('StockPackDetalle', StockPackDetalleController::class);
Route::resource('StockPendiente', StockPendienteController::class);
Route::resource('Configuracion', ConfiguracionController::class);
Route::resource('Locales', LocalController::class);
Route::resource('ResumenVenta', ResumenVentaController::class);
Route::resource('Factura', FacturaController::class);
Route::get('CierreDiaExport/csv', [CierreDiaController::class, 'exportCSV'])->name('CierreDia.exportCSV');
Route::resource('CierreDia', CierreDiaController::class);
Route::resource('Devolucion', DevolucionVentaController::class);
Route::resource('VentaAnalisis', VentaAnalisisController::class);
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::delete('stock-pendiente/{id}', [StockPendienteController::class, 'destroyPendiente'])->name('stock-pendiente.destroy');
Route::delete('StockPendiente/destroy-all', [StockPendienteController::class, 'destroyAll'])->name('StockPendiente.destroyAll');
