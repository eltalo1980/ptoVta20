<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;
use Carbon\Carbon;


class CierreDiaController extends Controller
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
    public function fncTraeListaFechas()
    {
        $tblCaja = (new ParametriaController)->fncTraeTablaCaja();
        $listaFechas = DB::select("
            SELECT distinct DATE_FORMAT(fecha_movimiento, '%Y%m%d') AS fecha_movimiento
            from " . $tblCaja . "
            where id_local= " . Auth::user()->id_local . "
            order by fecha_movimiento DESC
            ");

        return $listaFechas;
    }


    public function fncVentasBorradas($fecha)
    {
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        $TablaVentasDetalleTmpBorrada = (new ParametriaController)->fncTraeTablaVentasDetalleTmpBorrada();
        // ventas borradas por medio
        $ventasBorradasMedio = DB::select("
        SELECT
        DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            FORMAT((	SUM(precio_venta * cantidad) -  (SUM(precio_venta * cantidad)*($iva/100) )),0,'es_CL') AS neto,
            FORMAT(((SUM(precio_venta * cantidad)* ($iva/100)) ),0,'es_CL') 	AS IVA,
            FORMAT((SUM(precio_venta * cantidad)),0,'es_CL') AS Total
        FROM " . $TablaVentasDetalleTmpBorrada . "
        WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
        AND   id_local= " . Auth::user()->id_local . "
        GROUP BY forma_pago,DATE_FORMAT(fecha_venta, '%Y%m%d'); ");
        return $ventasBorradasMedio;
    }


    public function fncVentasPorMedio($fecha)
    {
        $tblVentasTotal = (new ParametriaController)->fncTraeTablaVentasTotales();

        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');

        // ventas por medio
        $ventasPorMedio = DB::select("
        SELECT
            DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            CASE
                WHEN forma_pago =1 THEN 'Tarjeta'
                WHEN forma_pago =2 THEN 'Efectivo'
                WHEN forma_pago =3 THEN 'Transferencia'
                WHEN forma_pago =4 THEN 'Venta Interna'
            END AS forma_pago,
            FORMAT((SUM(total_venta) - (SUM(total_venta)*($iva/100))),0,'es_CL')  AS neto,
            FORMAT((SUM(total_venta)*($iva/100)),0,'es_CL') AS iva,
            FORMAT((SUM(total_venta)),0,'es_CL') AS Total
        FROM " . $tblVentasTotal . "
        WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
        AND   id_local= " . Auth::user()->id_local . "
        GROUP BY forma_pago,DATE_FORMAT(fecha_venta, '%Y%m%d')");
        return $ventasPorMedio;
    }
    public function fncVentasPorMedioToTal($fecha)
    {
        $tblVentasTotal = (new ParametriaController)->fncTraeTablaVentasTotales();
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        $ventasPorMedioTotal = DB::select("
        SELECT
            DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            FORMAT((SUM(total_venta) - (SUM(total_venta)*($iva/100))),0,'es_CL')  AS neto,
            FORMAT((SUM(total_venta)*($iva/100)),0,'es_CL') AS iva,
            FORMAT((SUM(total_venta)),0,'es_CL') AS Total
        FROM " . $tblVentasTotal . "
        WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
        AND   id_local= " . Auth::user()->id_local . "
        GROUP BY DATE_FORMAT(fecha_venta, '%Y%m%d')");
        return $ventasPorMedioTotal;
    }

    public function fncVentasPorEmpresa($fecha)
    {
        $tblVentasTotal = (new ParametriaController)->fncTraeTablaVentas();
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        // VENTAS Empresa
        $ventasPorEmpresa = DB::select("
            SELECT
            DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            empresa,
            FORMAT((SUM(sub_total) - (SUM(sub_total)*($iva/100))),0,'es_CL') AS neto,
            FORMAT((SUM(sub_total)*($iva/100)),0,'es_CL') AS iva,
            FORMAT((SUM(sub_total)),0,'es_CL') AS Total
            FROM " . $tblVentasTotal . "
            WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
            AND   id_local= " . Auth::user()->id_local . "
            GROUP BY DATE_FORMAT(fecha_venta, '%Y%m%d'),empresa
            ORDER BY empresa");
        return $ventasPorEmpresa;
    }

    public function fncVentasPorProducto($fecha)
    {
        $tblVentasTotal = (new ParametriaController)->fncTraeTablaVentas();
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        // VENTAS Producto
        $ventasPorProducto = DB::select("
            SELECT
            DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            descripcion as Producto,
            count(*) as Cantidad,
            precio_venta,
            FORMAT((SUM(sub_total) - (SUM(sub_total)*($iva/100))),0,'es_CL') AS neto,
            FORMAT((SUM(sub_total)*($iva/100)),0,'es_CL') AS iva,
            FORMAT((SUM(sub_total)),0,'es_CL') AS Total
            FROM " . $tblVentasTotal . "
            WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
            and length(codigo) > 7
            AND   id_local= " . Auth::user()->id_local . "
            GROUP BY DATE_FORMAT(fecha_venta, '%Y%m%d'),descripcion,precio_venta
            ORDER BY descripcion");
        return $ventasPorProducto;
    }

    public function fncVentasPorProductoPeso($fecha)
    {
        $tblVentasTotal = (new ParametriaController)->fncTraeTablaVentas();
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        // VENTAS Producto
        $ventasPorProducto = DB::select("
            SELECT
            DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            CONCAT(SUBSTRING_INDEX(descripcion, ')', 1), ')') AS Producto,
            Sum(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(descripcion, '(', -1), ')', 1) AS DECIMAL(10,4))) as Cantidad,
            precio_venta,
            FORMAT((SUM(sub_total) - (SUM(sub_total)*($iva/100))),0,'es_CL') AS neto,
            FORMAT((SUM(sub_total)*($iva/100)),0,'es_CL') AS iva,
            FORMAT((SUM(sub_total)),0,'es_CL') AS Total
            FROM " . $tblVentasTotal . "
            WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
            and length(codigo) <= 7
            AND   id_local= " . Auth::user()->id_local . "
            GROUP BY DATE_FORMAT(fecha_venta, '%Y%m%d'),Producto,precio_venta
            ORDER BY Producto");
        return $ventasPorProducto;
    }




    public function fncVentasPorEmpresaTotal($fecha)
    {
        $tblVentasTotal = (new ParametriaController)->fncTraeTablaVentas();
        // VENTAS Empresa
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');

        $ventasPorEmpresaTotal = DB::select("
            SELECT
            DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
            FORMAT((SUM(sub_total) - (SUM(sub_total)*($iva/100))),0,'es_CL') AS neto,
            FORMAT((SUM(sub_total)*($iva/100)),0,'es_CL') AS iva,
            FORMAT((SUM(sub_total)),0,'es_CL') AS Total
            FROM " . $tblVentasTotal . "
            WHERE DATE_FORMAT(fecha_venta, '%Y%m%d') = '" . $fecha . "'
            AND   id_local= " . Auth::user()->id_local . "
            GROUP BY DATE_FORMAT(fecha_venta, '%Y%m%d')");
        return $ventasPorEmpresaTotal;
    }

    public function fncPagoPorEmpresa($fecha)
    {
        $tblFacturas = (new ParametriaController)->fncTraeTablaFacturas();

        $pagosPorEmpresa = DB::select("
                SELECT
                DATE_FORMAT(fecha_pago, '%Y%m%d') AS fecha_pago,
                empresa,
                FORMAT((SUM(factura_monto)),0,'es_CL') AS factura_monto
                FROM " . $tblFacturas . "
                WHERE DATE_FORMAT(fecha_pago, '%Y%m%d') = '" . $fecha . "'
                AND   id_local= " . Auth::user()->id_local . "
                GROUP BY DATE_FORMAT(fecha_pago, '%Y%m%d'),empresa
            ORDER BY empresa");
        return $pagosPorEmpresa;
    }
    public function fncPagoPorEmpresaTotal($fecha)
    {
        $tblFacturas = (new ParametriaController)->fncTraeTablaFacturas();

        $pagosPorEmpresaTotal = DB::select("
                SELECT
                DATE_FORMAT(fecha_pago, '%Y%m%d') AS fecha_pago,
                FORMAT((SUM(factura_monto)),0,'es_CL') AS factura_monto_total
                FROM " . $tblFacturas . "
                WHERE DATE_FORMAT(fecha_pago, '%Y%m%d') = '" . $fecha . "'
                AND   id_local= " . Auth::user()->id_local . "
                GROUP BY DATE_FORMAT(fecha_pago, '%Y%m%d')");
        return $pagosPorEmpresaTotal;
    }
    public function fncValorStockPorEmpresa()
    {
        $tblSotck = (new ParametriaController)->fncTraeTablaStock();
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        $listaValorStockPorEmpresa = DB::select("
        SELECT
            empresa,
            FORMAT((SUM(precio_costo*cantidad)),0,'es_CL') AS precio_costo,
            FORMAT((SUM(precio_costo*cantidad)*(" . $iva . "/100)),0,'es_CL') AS iva,
            FORMAT((SUM(precio_venta*cantidad)),0,'es_CL') AS precio_venta,
            FORMAT((SUM(precio_venta*cantidad) - SUM(precio_costo*cantidad) + SUM(precio_costo*cantidad)*(" . $iva . "/100)),0,'es_CL') AS ganancia_total
            FROM " . $tblSotck . "
        WHERE activo=1
        AND   id_local= " . Auth::user()->id_local . "
        GROUP BY empresa
        ORDER BY empresa");
        return $listaValorStockPorEmpresa;
    }

    public function fncValorStockTotal()
    {
        $tblSotck = (new ParametriaController)->fncTraeTablaStock();
        $iva = (new ParametriaController)->fncTraeConfiguracion('valorIVA');
        $listaValorStockTotal = DB::select("
        SELECT
            FORMAT((SUM(precio_costo*cantidad)),0,'es_CL') AS precio_costo,
            FORMAT((SUM(precio_costo*cantidad)*(" . $iva . "/100)),0,'es_CL') AS iva,
            FORMAT((SUM(precio_venta*cantidad)),0,'es_CL') AS precio_venta,
            FORMAT((SUM(precio_venta*cantidad) - SUM(precio_costo*cantidad) + SUM(precio_costo*cantidad)*(" . $iva . "/100)),0,'es_CL') AS ganancia_total
            FROM " . $tblSotck . "
        WHERE activo=1
        AND   id_local= " . Auth::user()->id_local . "
        ");
        return $listaValorStockTotal;
    }

    public function index(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        if (strlen($request->fechaCierre) > 0) {
            $diaConsulta = $request->fechaCierre;
        } else {
            $diaConsulta = Carbon::now()->format('Ymd');
        }

        $ventasPorMedio = $this->fncVentasPorMedio($diaConsulta);

        $ventasBorradas = $this->fncVentasBorradas($diaConsulta);
        $ventasPorMedioTotal = $this->fncVentasPorMedioToTal($diaConsulta);
        $ventasPorEmpresa = $this->fncVentasPorEmpresa($diaConsulta);
        $ventasPorEmpresaTotal = $this->fncVentasPorEmpresaTotal($diaConsulta);
        $pagosPorEmpresa = $this->fncPagoPorEmpresa($diaConsulta);
        $pagosPorEmpresaTotal = $this->fncPagoPorEmpresaTotal($diaConsulta);
        $listaValorStockTotal = $this->fncValorStockTotal();
        $ventasPorProducto = $this->fncVentasPorProducto($diaConsulta);
        $ventasPorProductoPeso = $this->fncVentasPorProductoPeso($diaConsulta);

        $listaFechas = $this->fncTraeListaFechas();
        return view('InformeVentas.index', compact('ventasPorMedio', 'ventasBorradas', 'ventasPorEmpresa', 'listaFechas', 'diaConsulta', 'ventasPorMedioTotal', 'ventasPorEmpresaTotal', 'pagosPorEmpresa', 'pagosPorEmpresaTotal',  'ventasPorProducto', 'ventasPorProductoPeso'));
    }

    public function exportCSV(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return redirect()->route('login');
        }

        $fecha = $request->get('fecha', Carbon::now()->format('Ymd'));

        $headers = [
            "Content-type"        => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=informe_ventas_$fecha.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($fecha) {
            $file = fopen('php://output', 'w');
            // Añadir BOM para que Excel reconozca UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // 1. VENTAS POR MEDIO
            fputcsv($file, ['VENTAS POR MEDIO'], ';');
            fputcsv($file, ['Fecha', 'Forma Pago', 'Neto', 'IVA', 'Total'], ';');
            $vpm = $this->fncVentasPorMedio($fecha);
            foreach ($vpm as $r) {
                fputcsv($file, [$r->fecha_venta, $r->forma_pago, $r->neto, $r->iva, $r->Total], ';');
            }
            fputcsv($file, ['', '', '', '', ''], ';');

            // 1b. VENTAS BORRADAS
            fputcsv($file, ['VENTAS BORRADAS'], ';');
            fputcsv($file, ['Fecha', 'Neto', 'IVA', 'Total'], ';');
            $vb = $this->fncVentasBorradas($fecha);
            foreach ($vb as $r) {
                fputcsv($file, [$r->fecha_venta, $r->neto, $r->IVA, $r->Total], ';');
            }
            fputcsv($file, ['', '', '', '', ''], ';');

            // 2. VENTAS POR EMPRESA
            fputcsv($file, ['VENTAS POR EMPRESA'], ';');
            fputcsv($file, ['Fecha', 'Empresa', 'Neto', 'IVA', 'Total'], ';');
            $vpe = $this->fncVentasPorEmpresa($fecha);
            foreach ($vpe as $r) {
                fputcsv($file, [$r->fecha_venta, $r->empresa, $r->neto, $r->iva, $r->Total], ';');
            }
            fputcsv($file, ['', '', '', '', ''], ';');

            // 3. VENTAS POR PRODUCTO (UNIDAD)
            fputcsv($file, ['VENTAS POR PRODUCTO (UNIDAD)'], ';');
            fputcsv($file, ['Producto', 'Precio', 'Cantidad', 'Neto', 'IVA', 'Total'], ';');
            $vppu = $this->fncVentasPorProducto($fecha);
            foreach ($vppu as $r) {
                fputcsv($file, [$r->Producto, $r->precio_venta, $r->Cantidad, $r->neto, $r->iva, $r->Total], ';');
            }
            fputcsv($file, ['', '', '', '', ''], ';');

            // 4. VENTAS POR PRODUCTO (PESO)
            fputcsv($file, ['VENTAS POR PRODUCTO (PESO)'], ';');
            fputcsv($file, ['Producto', 'Precio', 'Cantidad', 'Neto', 'IVA', 'Total'], ';');
            $vppp = $this->fncVentasPorProductoPeso($fecha);
            foreach ($vppp as $r) {
                fputcsv($file, [$r->Producto, $r->precio_venta, $r->Cantidad, $r->neto, $r->iva, $r->Total], ';');
            }
            fputcsv($file, ['', '', '', '', ''], ';');

            // 5. PAGOS POR EMPRESA
            fputcsv($file, ['PAGOS POR EMPRESA'], ';');
            fputcsv($file, ['Fecha', 'Empresa', 'Monto'], ';');
            $ppe = $this->fncPagoPorEmpresa($fecha);
            foreach ($ppe as $r) {
                fputcsv($file, [$r->fecha_pago, $r->empresa, $r->factura_monto], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
