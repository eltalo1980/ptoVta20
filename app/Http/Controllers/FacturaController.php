<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use Carbon\Carbon;

use App\User;
use App\Stock;

class FacturaController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }
    public function fncFacturaTraeFechas()
    {
        $tableFacturas = (new ParametriaController)->fncTraeTablaFacturas();
        $fechasFacturas = DB::select("SELECT distinct DATE_FORMAT(fecha_pago, '%Y%m%d') AS fecha_pago
        FROM " . $tableFacturas . "
        WHERE id_local = " . Auth::user()->id_local . "
        ORDER BY fecha_pago DESC
        LIMIT 31;");
        return $fechasFacturas;
    }
    public function fncFacturaTraeDia($fecha)
    {

        $tableFacturas = (new ParametriaController)->fncTraeTablaFacturas();
        $listaPagos = DB::select("SELECT DATE_FORMAT(fecha_pago, '%Y%m%d') AS fecha_pago,
        empresa,
        FORMAT(factura_monto,0,'es_CL') as factura_monto
        FROM " . $tableFacturas . "
        WHERE id_local = " . Auth::user()->id_local . "
        AND DATE_FORMAT(fecha_pago, '%Y%m%d') = " . $fecha . "
        ORDER BY empresa ASC");
        return $listaPagos;
    }

    public function index(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $listaFechasFacturas = $this->fncFacturaTraeFechas();
        $listaEmpresas = (new StockController)->fncTraeEmpresas();
        if ($request->fechaFactura != "") {
            $listaPagos = $this->fncFacturaTraeDia($request->fechaFactura);
        } else {
            $date = Carbon::now();
            $formatedDate = $date->format('Ymd');
            $listaPagos = $this->fncFacturaTraeDia($formatedDate);
        }

        return view('Factura.index', compact('listaEmpresas', 'listaFechasFacturas', 'listaPagos'));
    }

    public function create()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $listaFechasFacturas = $this->fncFacturaTraeFechas();
        $listaEmpresas = (new StockController)->fncTraeEmpresas();
        return view('Factura.create', compact('listaEmpresas', 'listaFechasFacturas'));
    }
    public function store(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $date = Carbon::now();
        $formatedDate = $date->format('Ymd');
        $tableFacturas = (new ParametriaController)->fncTraeTablaFacturas();
        $tableLog = (new ParametriaController)->fncTraeTablaLog();
        DB::table($tableFacturas)
            ->insert(
                [
                    'id_local'        => Auth::user()->id_local,
                    'empresa'         => $request->cmbEmpresa,
                    'factura_monto'   => $request->factura_monto,
                    'fecha_pago'      => $formatedDate
                ]
            );

        $listaFechasFacturas = $this->fncFacturaTraeFechas();
        $listaEmpresas = (new StockController)->fncTraeEmpresas();
        $listaPagos = $this->fncFacturaTraeDia($formatedDate);
        return view('Factura.index', compact('listaEmpresas', 'listaFechasFacturas', 'listaPagos'));
    }
}
