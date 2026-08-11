<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $tablePagos = (new ParametriaController)->fncTraeTablaPagos();
        $pago =  DB::select("SELECT * FROM $tablePagos where id_local = " . auth()->user()->id_local . " and now() between fecha_inicio and fecha_fin");

        if (count($pago) == 0) {
            $mensaje = 'No se Registran Pagos de la aplicacion para este Mes';
            return redirect()->route('venta.index')->with(['Mensaje' => $mensaje, 'Estilo' => 'alert alert-danger']);
        }

        return view('home');
    }
}
