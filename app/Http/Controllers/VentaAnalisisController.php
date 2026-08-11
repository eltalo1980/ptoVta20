<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;

use function PHPUnit\Framework\isNull;

class VentaAnalisisController extends Controller
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

    public function fncTablas($codigo)
    {
        $tableVentasTotales = (new ParametriaController)->fncTraeTablaVentasTotales();
        $tableVentas = (new ParametriaController)->fncTraeTablaVentas();
    }

    public function fncTotalVentasPorDia($fini, $ffin)
    {
        $tableVentasTotales = (new ParametriaController)->fncTraeTablaVentasTotales();
        return DB::select("SELECT DATE_FORMAT(fecha_venta,'%Y%m%d') AS fecha,SUM(total_venta) AS total 
        FROM " . $tableVentasTotales . "
        WHERE fecha_venta BETWEEN '" . $fini . "' AND '" . $ffin . "'
        GROUP BY DATE_FORMAT(fecha_venta,'%Y%m%d')
        ORDER BY DATE_FORMAT(fecha_venta,'%Y%m%d');");
    }

    public function fncHoraMayorVentasPorDia($fini, $ffin)
    {
        $tableVentasTotales = (new ParametriaController)->fncTraeTablaVentasTotales();
        return DB::select("SELECT DATE_FORMAT(fecha_venta,'%k') AS hora,SUM(total_venta) AS total 
        FROM " . $tableVentasTotales . "
        WHERE fecha_venta BETWEEN '" . $fini . "' AND '" . $ffin . "'
        GROUP BY hora
        ORDER BY hora asc;");
    }

    public function fncRankingProductoMasVendido($fini, $ffin)
    {
        $tableVentas = (new ParametriaController)->fncTraeTablaVentas();
        return DB::select("SELECT 
        TRIM(SUBSTRING_INDEX(descripcion, '(', 1)) AS descripcion_limpia, 
        COUNT(1) AS cantidad,
        sum(sub_total) AS total_venta
        FROM " . $tableVentas . "
        WHERE fecha_venta BETWEEN '" . $fini . "' AND '" . $ffin . "'
        GROUP BY descripcion_limpia
        ORDER BY cantidad DESC
        limit 20;
        ");
    }

    public function index(Request $rq)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        // Obtener fechas del request, o usar hoy por defecto
        $hoy = now()->format('Y-m-d');
        //$hoy = '2025-01-20';
        $fini = $rq->input('fecha_inicio', $hoy . ' 00:00:00');
        $ffin = $rq->input('fecha_final', $hoy . ' 23:59:59');

        // Si solo viene la fecha sin hora, agregar horas por defecto
        if (strlen($fini) == 10) $fini .= ' 00:00:00';
        if (strlen($ffin) == 10) $ffin .= ' 23:59:59';

        $ventasPorDia = $this->fncTotalVentasPorDia($fini, $ffin);
        $horaMayorVentas = $this->fncHoraMayorVentasPorDia($fini, $ffin);
        $rankingProductos = $this->fncRankingProductoMasVendido($fini, $ffin);
        return view('VentaAnalisis.index', compact('ventasPorDia', 'horaMayorVentas', 'rankingProductos'));
    }

    public function store(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $xaccion = $request->accion;
        $totalTmp1 = 0;
        $tableVentaDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();
        $Mensaje = '';
        $Estilo = '';

        //dd($request);

        //dd("sacar los productos con cod 2100000");
        // ver el tema de los pesos.... masa
        //if(substr($request->codigo,0,5)=="21000")
        if (substr($request->codigo, 0, 3) == "210") {
            //echo "paso1";
            $divisor = (new ParametriaController)->fncTraeConfiguracion('divisorPeso');
            $codigo = substr($request->codigo, 0, 7);
            //dd($codigo,$divisor);
            if (!is_null($divisor)) // Si tengo definida la variable
            {
                //echo "paso0";
                $listadoProductos = $this->fncTraerProductosCodigo($codigo);
                if ($listadoProductos[0]->venta_por_unidad == 1) {
                    $peso = 1;
                    $cantidad = 1;
                } else {
                    $peso = substr($request->codigo, 7, 6) / (10000 * $divisor);
                    $cantidad = substr($request->codigo, 7, 6) / (10000 * $divisor);
                }
                if (count($listadoProductos) > 0) {
                    $descripcion = $listadoProductos[0]->descripcion . "(" . $listadoProductos[0]->precio_venta . ")(" . $peso . ")";
                    $precio_venta = ($listadoProductos[0]->precio_venta);
                    $empresa = $listadoProductos[0]->empresa;
                    $cantidad_stock  = $listadoProductos[0]->cantidad_stock;
                }
            } else {
                //echo "paso2";
                //dd("aaaaa");
                $peso = substr($request->codigo, 7, 6) / (10000);
                $cantidad = substr($request->codigo, 7, 6) / 10000;
                //dd($peso,$cantidad);
                $listadoProductos = $this->fncTraerProductosCodigo($codigo);
                if (count($listadoProductos) > 0) {
                    $descripcion = $listadoProductos[0]->descripcion . "(" . $listadoProductos[0]->precio_venta . ")(" . $peso . ")";
                    $precio_venta = ($listadoProductos[0]->precio_venta);
                    $empresa = $listadoProductos[0]->empresa;
                    $cantidad_stock  = $listadoProductos[0]->cantidad_stock;
                }
            }
            //dd($peso,$cantidad,$listadoProductos,$descripcion,$precio_venta,$empresa,$cantidad_stock,($precio_venta*$cantidad));
        } else {
            //echo "paso3";
            $codigo = $request->codigo;
            $listadoProductos = $this->fncTraerProductosCodigo($codigo);
            //dd($listadoProductos);
            $cantidad = 1;
            if (count($listadoProductos) == 1) {
                if (($listadoProductos[0]->cantidad_venta_mayor > 0) && count($listadoProductos) >= $listadoProductos[0]->cantidad_venta_mayor) {
                    $precio_venta = $listadoProductos[0]->precio_venta_mayor;
                } else {
                    $precio_venta = $listadoProductos[0]->precio_venta;
                }
                $descripcion = $listadoProductos[0]->descripcion;
                $empresa = $listadoProductos[0]->empresa;
                $cantidad_stock  = $listadoProductos[0]->cantidad_stock;
            }
        }

        if (count($listadoProductos) == 1) {

            //echo "paso4";
            $cantidadProdVentaTMP = $this->fncBuscarProductoVentasTMP($codigo);
            $productoTMP = $this->fncTraeProductoVentasTMP($codigo);
            $infoProducto = $this->fncTraerProductosCodigo($codigo);


            //dd($cantidadProdVentaTMP,$xaccion,$productoTMP,$infoProducto);
            if ($xaccion == "addProducto" or $cantidadProdVentaTMP > 0) {
                $aaa  = intval($cantidadProdVentaTMP) + 1;
                //dd($infoProducto,$aaa);
                if ($infoProducto[0]->cantidad_venta_mayor > 0) {
                    if ($aaa >= $infoProducto[0]->cantidad_venta_mayor) {
                        $precio_venta = $infoProducto[0]->precio_venta_mayor;
                    } else {
                        $precio_venta = $infoProducto[0]->precio_venta;
                    }
                } else {
                    $precio_venta = $infoProducto[0]->precio_venta;
                }


                $subTot = ($precio_venta) * $aaa;
                //$subTot = ($precio_venta) * $aaa;
                //dd($precio_venta,$infoProducto,$aaa,$subTot);
                //echo "mayor ".$subTot;
                if (substr($request->codigo, 0, 3) == "210") {
                    $resultado = DB::select("UPDATE " . $tableVentaDetalleTmp . " SET cantidad = " . $aaa . ",
                    precio_venta = " . $precio_venta . ",
                    sub_total =  " . $subTot . "
                    WHERE id_usuario = " . Auth::user()->id_usuario . "
                    AND id_local = " . Auth::user()->id_local . "
                    AND substr(codigo,1,7) = '" . substr($request->codigo, 0, 7) . "';");
                } else {
                    $resultado = DB::select("UPDATE " . $tableVentaDetalleTmp . " SET cantidad = " . $aaa . ",
                    precio_venta = " . $precio_venta . ",
                    sub_total =  " . $subTot . "
                    WHERE id_usuario = " . Auth::user()->id_usuario . "
                    AND id_local = " . Auth::user()->id_local . "
                    AND trim(codigo) = '" . trim($request->codigo) . "';");
                }


                //dd($aaa,$precio_venta,$subTot,$resultado);
                $Mensaje = 'Producto Agregado !!!';
                $Estilo = 'alert alert-success';
            }

            if ($xaccion == "delProducto") {
                $aaa  = intval($cantidadProdVentaTMP) - 1;
                if ($aaa >= $infoProducto[0]->cantidad_venta_mayor) {
                    $precio_venta = $infoProducto[0]->precio_venta_mayor;
                } else {
                    $precio_venta = $infoProducto[0]->precio_venta;
                }

                //$subTot = ($productoTMP[0]->precio_venta) * $aaa;
                $subTot = ($precio_venta) * $aaa;
                DB::select("UPDATE " . $tableVentaDetalleTmp . " SET cantidad = " . $aaa . " ,
                    precio_venta = " . $precio_venta . ",
                    sub_total = " . $subTot . "
                    WHERE id_usuario = " . Auth::user()->id_usuario . "
                    AND  id_local = " . Auth::user()->id_local . "
                    AND trim(codigo) = '" . trim($request->codigo) . "';");
                $Mensaje = 'Producto Eliminado !!!';
                $Estilo = 'alert alert-warning';
            }

            //dd($xaccion, $cantidadProdVentaTMP);
            if ($xaccion == "" and $cantidadProdVentaTMP == 0) {
                //echo "paso5";
                if (count($listadoProductos) > 0) {
                    DB::table($tableVentaDetalleTmp)->insert([
                        [
                            'id_local'      => Auth::user()->id_local,
                            'id_usuario'    => Auth::user()->id_usuario,
                            'codigo'        => $codigo,
                            'empresa'       => $empresa,
                            'descripcion'   => $descripcion,
                            'fecha_venta'   => now(),
                            'precio_venta'  => $precio_venta,
                            'cantidad'      => $cantidad,
                            'stock'         => $cantidad_stock,
                            'sub_total'     => $precio_venta * $cantidad
                        ]
                    ]);
                } else {
                    $Mensaje = 'Producto no Encontrado!!!';
                    $Estilo = 'alert alert-danger';
                }
            }
            $listadoProductos = null;
        }

        $ventaTmp = DB::select("SELECT
        codigo,descripcion,
        CASE
            WHEN substring(CONVERT(cantidad , CHAR), INSTR(CONVERT(cantidad , CHAR) , '.')+1 ,4) = '0000' THEN substring(CONVERT(cantidad , CHAR), 1, INSTR(CONVERT(cantidad , CHAR) , '.')-1)
            ELSE CONVERT(cantidad , CHAR)
        END AS cantidad,
        FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori , FORMAT((cantidad*precio_venta),0,'es_CL') AS precio_venta,
        sum(stock) as stock
        FROM " . $tableVentaDetalleTmp . "
        WHERE id_usuario = " . Auth::user()->id_usuario . "
        AND  id_local = " . Auth::user()->id_local . "
        GROUP BY codigo,descripcion,precio_venta,cantidad
        ;");

        $totalTmp1 = DB::select("SELECT
        FORMAT(SUM(cantidad*precio_venta),0,'es_CL') AS total
        FROM " . $tableVentaDetalleTmp . "
        WHERE id_usuario = " . Auth::user()->id_usuario . "
        AND  id_local = " . Auth::user()->id_local . "
        ;");


        $totalTmp = $totalTmp1[0]->total;

        $totalDevolucion = (new DevolucionVentaController)->fncTraertotalDevolucion();

        $TotaFinal = number_format((int)((intval(str_replace('.', '', $totalTmp)) - intval(str_replace('.', '', $totalDevolucion)))), 0, '', '.');

        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

        //$buttonSize= (new ParametriaController)->fncButtonSize();
        //$textSize= (new ParametriaController)->fncTextoSize();
        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        return view('Ventas.index', compact('listadoProductos', 'ventaTmp', 'totalTmp', 'totalDevolucion', 'TotaFinal', 'Mensaje', 'Estilo', 'codBarraEfectivo', 'codBarraTarjeta', 'cajaPagadora', 'userNivel'));
    }
    /////////////////////////////////////////////////////
    public function destroy(Request $request, $id)
    {

        dd($id, $request);
        $totalTmp1 = 0;
        $table = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        DB::select("DELETE FROM $table
        WHERE id_usuario = " . Auth::user()->id_usuario . "
        AND  id_local = " . Auth::user()->id_local . "
        AND codigo = '" . $id . "';");

        //DB::delete('delete from '.$table.' where id_local = ? and id_usuario and codigo =?', [Auth::user()->id_local],[Auth::user()->id_usuario],[$id]);

        $ventaTmp = DB::table($table)
            ->select('precio_venta', 'codigo', 'descripcion')
            ->where('id_local', '=', Auth::user()->id_local)
            ->where('id_usuario', '=', Auth::user()->id_usuario)
            ->get();

        $listadoProductos = $this->fncTraerProductos();

        foreach ($ventaTmp as $vtmp) {
            $totalTmp1 += $vtmp->precio_venta;
        }

        $totalTmp = $this->fncFormatoMoneda($totalTmp1);
        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

        //$buttonSize= (new ParametriaController)->fncButtonSize();
        //$textSize= (new ParametriaController)->fncTextoSize();
        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        return view('Ventas.index', compact('listadoProductos', 'ventaTmp', 'totalTmp', 'codBarraEfectivo', 'codBarraTarjeta', 'cajaPagadora', 'userNivel'));
    }
    public function show($id)
    {
        $totalTmp1 = 0;
        $tableVtasDetalle = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();
        DB::delete('delete from ' . $tableVtasDetalle . ' where codigo = "' . $id . '" and id_local = ' . Auth::user()->id_local . ' and id_usuario =' . Auth::user()->id_usuario . ' limit 1;');

        $ventaTmp = DB::select("SELECT
        codigo,descripcion, CAST(cantidad AS UNSIGNED) as cantidad ,  FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori , FORMAT((cantidad*precio_venta),0,'es_CL') AS precio_venta,  sum(stock) AS stock
        FROM " . $tableVtasDetalle . "
        WHERE id_usuario = " . Auth::user()->id_usuario . "
        AND  id_local = " . Auth::user()->id_local . "
        GROUP BY codigo,descripcion,precio_venta,cantidad
        ;");


        $totalTmp1 = DB::select("SELECT
        FORMAT(SUM(cantidad*precio_venta),0,'es_CL') AS total
        FROM " . $tableVtasDetalle . " 
        WHERE id_usuario = " . Auth::user()->id_usuario . "
        AND  id_local = " . Auth::user()->id_local . "
        ;");
        //$listadoProductos = $this->fncTraerProductos();
        $listadoProductos = null;
        $totalTmp = $totalTmp1[0]->total;
        //$buttonSize= (new ParametriaController)->fncButtonSize();
        //$textSize= (new ParametriaController)->fncTextoSize();

        //$totalTmp = $this->fncFormatoMoneda($totalTmp1[0]->total);
        $totalDevolucion = (new DevolucionVentaController)->fncTraertotalDevolucion();

        $TotaFinal = number_format((int)((intval(str_replace('.', '', $totalTmp)) - intval(str_replace('.', '', $totalDevolucion)))), 0, '', '.');
        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        return view('Ventas.index', compact('listadoProductos', 'ventaTmp', 'totalTmp', 'totalDevolucion', 'TotaFinal', 'codBarraEfectivo', 'codBarraTarjeta', 'cajaPagadora', 'userNivel'));
    }
}
