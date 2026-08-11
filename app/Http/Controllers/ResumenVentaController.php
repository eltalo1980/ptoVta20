<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;
use Carbon\Carbon;

class ResumenVentaController extends Controller
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
    public function fncTraerTotales($idVenta)
    {
        $tableVentasTotal = (new ParametriaController)->fncTraeTablaVentasTotales();
        $tableUsuarios = (new ParametriaController)->fncTraeTablaUsuarios();
        $diaHoy = Carbon::now()->format('Ymd');

        if (strlen($idVenta) == 0) {
            $fechaMax = DB::select("SELECT DATE_FORMAT(max(fecha_venta), '%Y%m%d') as fechaMax FROM " . $tableVentasTotal . ";");

            $listadoVentas = DB::select("
            SELECT concat(B.nombres,' ', B.apellidos) AS nombres ,DATE_FORMAT(A.fecha_venta, '%Y%m%d %H:%i:%s') AS fecha_venta, 
            case    when A.forma_pago=1 then 'Tarjeta'
                    when A.forma_pago=2 then 'Efectivo'
                    when A.forma_pago=3 then 'Transferencia'
                    when A.forma_pago=4 then 'Venta Interna'
            end as forma_pago, 
            (A.total_venta) as total_venta, A.monto_sencillo as monto_sencillo, A.vuleto as vuleto, A.id_ventas
            from " . $tableVentasTotal . " A
            INNER JOIN " . $tableUsuarios . " B ON A.id_usuario = B.id_usuario
            WHERE DATE_FORMAT(A.fecha_venta, '%Y%m%d') = '" . $fechaMax[0]->fechaMax . "'
            AND   A.id_local = " . Auth::user()->id_local . "
            ORDER BY A.fecha_venta DESC
            ;");
        } else {
            $listadoVentas = DB::select("
            SELECT concat(B.nombres,' ', B.apellidos) AS nombres ,DATE_FORMAT(A.fecha_venta, '%Y%m%d %H:%i:%s') AS fecha_venta, 
            case    when A.forma_pago=1 then 'Tarjeta'
                    when A.forma_pago=2 then 'Efectivo'
                    when A.forma_pago=3 then 'Transferencia'
                    when A.forma_pago=4 then 'Venta Interna'
            end as forma_pago
            ,   (A.total_venta) as total_venta,  A.monto_sencillo as monto_sencillo, A.vuleto as vuleto, A.id_ventas
            from " . $tableVentasTotal . " A
            INNER JOIN " . $tableUsuarios . " B ON A.id_usuario = B.id_usuario
            WHERE A.id_ventas = " . $idVenta . "
            AND   A.id_local = " . Auth::user()->id_local . "
            ;");
        }
        return $listadoVentas;
    }


    public function fncTraerdetalles($idVenta)
    {
        $tableDetalle = (new ParametriaController)->fncTraeTablaVentas();

        $detalleVentas = DB::select("
        SELECT codigo, 
        descripcion, 
        DATE_FORMAT(fecha_venta, '%Y%m%d %H:%i:%s') AS fecha_venta, 
        precio_venta as precio_venta, 
        sum(cantidad) as cantidad,
        sum(sub_total) as sub_total
        from " . $tableDetalle . "
        WHERE id_ventas_total = " . $idVenta . "
        AND   id_local = " . Auth::user()->id_local . "
        GROUP BY codigo,descripcion,fecha_venta,precio_venta
        ;");
        return $detalleVentas;
    }

    public function fncTraerdetallesPorDia($fecha)
    {
        $tableVentasTotal = (new ParametriaController)->fncTraeTablaVentasTotales();
        $tableUsuarios = (new ParametriaController)->fncTraeTablaUsuarios();

        $listadoVentas = DB::select("
        SELECT concat(B.nombres,' ', B.apellidos) AS nombres ,
            DATE_FORMAT(A.fecha_venta, '%Y%m%d %H:%i:%s') AS fecha_venta, 
        case when A.forma_pago=1 then 'Tarjeta'
             when A.forma_pago=2 then 'Efectivo'
             when A.forma_pago=3 then 'Transferencia'
             when A.forma_pago=4 then 'Venta Interna'
            end as forma_pago
        
        , A.total_venta as total_venta, A.monto_sencillo as monto_sencillo, A.vuleto as vuleto, A.id_ventas
        from " . $tableVentasTotal . " A
        INNER JOIN " . $tableUsuarios . " B ON A.id_usuario = B.id_usuario
        WHERE DATE_FORMAT(A.fecha_venta, '%Y%m%d') = '" . $fecha . "'
        AND   A.id_local = " . Auth::user()->id_local . "
        ORDER BY A.fecha_venta DESC
        ;");

        return $listadoVentas;
    }

    public function fncTraerListaFechaVenta()
    {
        $tableDetalle = (new ParametriaController)->fncTraeTablaVentas();
        $listaFechas = DB::select("
        SELECT distinct DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta
        from " . $tableDetalle . "
        WHERE id_local= " . Auth::user()->id_local . "
        order by DATE_FORMAT(fecha_venta, '%Y%m%d') desc
        limit 31
        ;");
        return $listaFechas;
    }

    public function index(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $fechaVenta = trim($request->get('fecha_venta'));
        $listaFechas = null;
        $listadoDetalle = null;
        $listadoVentas = null;
        if (strlen($fechaVenta) > 0) {
            $listadoVentas = $this->fncTraerdetallesPorDia($fechaVenta);
        }

        $idVenta = trim($request->get('idVenta'));
        if (strlen($idVenta) > 0) {
            $listadoDetalle = $this->fncTraerdetalles($idVenta);
        }

        $listaFechas = $this->fncTraerListaFechaVenta();


        return view('ResumenVenta.index', compact('listadoVentas', 'listadoDetalle', 'listaFechas'));
    }

    public function edit(Request $request, $idVenta)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        $listadoVentas = $this->fncTraerTotales($idVenta);
        $listadoDetalle = $this->fncTraerdetalles($idVenta);
        $listaFechas = $this->fncTraerListaFechaVenta();
        return view('ResumenVenta.index', compact('listadoVentas', 'listadoDetalle', 'listaFechas'));
    }
}
