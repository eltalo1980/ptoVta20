<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;

use function PHPUnit\Framework\isNull;

class VentaController extends Controller
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
    //(new VentaController)->fncFormatoMoneda
    public function fncFormatoMoneda($valor)
    {
        $valor = str_replace('-', '', $valor);

        if (strlen($valor) <= 3) {
            $aa = $valor;
        }
        if (strlen($valor) >= 4) {
            $aa = substr($valor, 0, 1) . '.' . substr($valor, 1, 3);
        }
        if (strlen($valor) == 5) {
            $aa = substr($valor, 0, 2) . '.' . substr($valor, 2, 3);
        }
        if (strlen($valor) == 6) {
            $aa = substr($valor, 0, 3) . '.' . substr($valor, 3, 3);
        }
        return $aa;
    }


    public function fncTraerProductosCodigo($codigo)
    {
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $listadoProductos = DB::select("
        SELECT codigo,descripcion,precio_venta,empresa , cantidad as cantidad_stock, cantidad_minima,cantidad_venta_mayor,precio_venta_mayor,venta_por_unidad
        FROM " . $tableStock . "
        WHERE id_local = " . Auth::user()->id_local . "
        AND activo =1
        AND codigo = '" . $codigo . "'
        ORDER BY codigo");

        if (count($listadoProductos) == 0) {
            $listadoProductos = DB::select("
            SELECT codigo,descripcion,precio_venta,empresa , cantidad as cantidad_stock, cantidad_minima,cantidad_venta_mayor,precio_venta_mayor,venta_por_unidad
            FROM " . $tableStock . "
            WHERE id_local = " . Auth::user()->id_local . "
            AND activo =1
            AND codigo like '%" . $codigo . "%'
            ORDER BY codigo");
        }
        //informacion del Pack
        if (count($listadoProductos) == 0) {
            $TablaStockPack = (new ParametriaController)->fncTraeTablaStockPack();
            $listadoProductos = DB::select("
            SELECT codigo_pack,descripcion,precio_venta,empresa , cantidad as cantidad_stock, cantidad_minima
            FROM " . $TablaStockPack . "
            WHERE id_local = " . Auth::user()->id_local . "
            AND activo =1
            AND codigo =0
            AND codigo_pack = '" . $codigo . "'");
        }
        return $listadoProductos;
    }

    public function fncBuscarProductoVentasTMP($codigo)
    {
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $table = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "SELECT sum(cantidad) as totalCantidad FROM " . $table . "
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local . "
        AND trim(codigo) = '" . trim($codigo) . "'";

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        $result = DB::select($sql . ";");

        return $result[0]->totalCantidad ?? 0;
    }

    public function fncTraeProductoVentasTMP($codigo)
    {
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $table = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "SELECT * FROM " . $table . "
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local . "
        AND trim(codigo) = '" . trim($codigo) . "'";

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        return DB::select($sql . ";");
    }

    public function fncTraerTotalVentaTmp()
    {
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableVentaTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "SELECT sum(sub_total) as total FROM " . $tableVentaTmp . "
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local;

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        $totalVentaTmp = DB::select($sql . ";");
        return $totalVentaTmp[0]->total;
    }

    public function fncTraerlistaVentasTmp()
    {
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableVtasTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "SELECT * FROM $tableVtasTmp
        WHERE id_local = " . Auth::user()->id_local . " AND id_usuario =" . $id_query;

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        return DB::select($sql);
    }


    public function fncTraerProductos()
    {
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $listadoProductos = DB::table($tableStock)
            ->select('codigo', 'descripcion', 'precio_venta', 'empresa')
            ->orderBy('codigo', 'ASC')
            ->get();
        return $listadoProductos;
    }

    public function fncVentasRespaldasVentasNoRealizadas()
    {
        $tableVtasTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();
        $tableVtasTmpLog = (new ParametriaController)->fncTraeTablaVentasDetalleTmpBorrada();

        DB::select("INSERT INTO " . $tableVtasTmpLog . " (id_ventas, id_local, id_usuario, id_producto, codigo, descripcion, fecha_venta, precio_venta, cantidad, forma_pago, sub_total, empresa, stock, ultima_actualizacion, actualizacion_estado)
        SELECT id_ventas, id_local, id_usuario, id_producto, codigo, descripcion, fecha_venta, precio_venta, cantidad, forma_pago, sub_total, empresa, stock, ultima_actualizacion, actualizacion_estado 
        from " . $tableVtasTmp . "
        WHERE id_local = " . Auth::user()->id_local . " AND id_usuario =" . Auth::user()->id_usuario);

        DB::delete("DELETE from " . $tableVtasTmp . "
        WHERE id_local = " . Auth::user()->id_local . " and id_usuario =" . Auth::user()->id_usuario);
    }
    public function index(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        if ($request->has('clear_folio')) {
            session()->forget(['folio_caja', 'vendedor_id_original']);
        }

        if ($request->has('folio')) {
            $folio = $request->folio;
            session(['folio_caja' => $folio]);

            $tableVtasTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();
            $primerItem = DB::table($tableVtasTmp)
                ->where('folio_caja', $folio)
                ->where('id_local', Auth::user()->id_local)
                ->first();

            if ($primerItem) {
                session(['vendedor_id_original' => $primerItem->id_usuario]);
            }
        }

        $listadoProductos = null;
        $ventaTmp = null;
        $totalTmp = null;
        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');
        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;

        $Mensaje = session('Mensaje');
        $Estilo = session('Estilo');

        // Si hay una venta activa (en curso o de folio), la cargamos para mostrarla en el index
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableVentaDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $query = DB::table($tableVentaDetalleTmp)
            ->where('id_local', Auth::user()->id_local)
            ->where('id_usuario', $id_query);

        if ($folio_query) {
            $query->where('folio_caja', $folio_query);
        } else {
            $query->where('estado_caja', 0);
        }

        // Clonamos la query para calcular el total sin el groupBy de la lista
        $queryTotal = clone $query;

        $ventaTmp = $query->select(
            'codigo',
            'descripcion',
            'precio_venta',
            DB::raw("CASE 
                    WHEN substring(CONVERT(SUM(cantidad) , CHAR), INSTR(CONVERT(SUM(cantidad) , CHAR) , '.')+1 ,4) = '0000' THEN substring(CONVERT(SUM(cantidad) , CHAR), 1, INSTR(CONVERT(SUM(cantidad) , CHAR) , '.')-1)
                    ELSE CONVERT(SUM(cantidad) , CHAR)
                END AS cantidad"),
            DB::raw("FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori"),
            DB::raw("FORMAT((SUM(cantidad)*precio_venta),0,'es_CL') AS precio_venta"),
            DB::raw("SUM(stock) as stock")
        )
            ->groupBy('codigo', 'descripcion', 'precio_venta')
            ->get();

        $totalTmpRes = $queryTotal->select(DB::raw("FORMAT(SUM(cantidad*precio_venta),0,'es_CL') AS total"))->first();
        $totalTmp = $totalTmpRes ? $totalTmpRes->total : '0';

        $totalDevolucion = (new DevolucionVentaController)->fncTraertotalDevolucion();
        $TotaFinal = number_format((int)((intval(str_replace('.', '', $totalTmp)) - intval(str_replace('.', '', $totalDevolucion)))), 0, '', '.');

        $consultaPrecio = (new ParametriaController)->fncTraeConfiguracion('consultaPrecio');

        return view('Ventas.index', compact('listadoProductos', 'ventaTmp', 'totalTmp', 'totalDevolucion', 'TotaFinal', 'codBarraEfectivo', 'codBarraTarjeta', 'Mensaje', 'Estilo', 'cajaPagadora', 'userNivel', 'consultaPrecio'));
    }

    public function store(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        $id_usuario_actual = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_caja = session('folio_caja');

        $xaccion = $request->accion;
        $totalTmp1 = 0;
        $tableVentaDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');
        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        $codigoLimpio = trim(substr($request->codigo, 0, 7));

        // Impedir que los códigos de pago se registren como productos normales
        if ($codigoLimpio == trim($codBarraEfectivo) || $codigoLimpio == trim($codBarraTarjeta)) {
            $metodo = ($codigoLimpio == trim($codBarraEfectivo)) ? 'codBarraEfectivo' : 'codBarraTarjeta';

            // Actualizar el tipo_pago para los productos actuales en la venta temporal
            DB::table($tableVentaDetalleTmp)
                ->where('id_usuario', $id_usuario_actual)
                ->where('id_local', Auth::user()->id_local)
                ->where('estado_caja', 0)
                ->update(['tipo_pago' => $metodo]);

            // Si es vendedor y Caja Pagadora está activa, realizar el parking
            if ($cajaPagadora == 1 && $userNivel < 10) {
                $tieneProductos = DB::table($tableVentaDetalleTmp)
                    ->where('id_usuario', $id_usuario_actual)
                    ->where('id_local', Auth::user()->id_local)
                    ->where('estado_caja', 0)
                    ->exists();

                if ($tieneProductos) {
                    $folio = 'F' . $id_usuario_actual . '-' . time();
                    DB::table($tableVentaDetalleTmp)
                        ->where('id_usuario', $id_usuario_actual)
                        ->where('id_local', Auth::user()->id_local)
                        ->where('estado_caja', 0)
                        ->update([
                            'estado_caja' => 1,
                            'folio_caja' => $folio
                        ]);

                    return redirect()->route('venta.index')->with('Mensaje', 'Venta enviada a Caja (' . ($metodo == 'codBarraEfectivo' ? 'EFECTIVO' : 'TARJETA') . ')')->with('Estilo', 'alert alert-success');
                }
            }

            // Si no es el caso de parking, igual retornamos para no registrar el código como producto
            return redirect()->route('venta.index')->with('Mensaje', 'Tipo de pago registrado: ' . ($metodo == 'codBarraEfectivo' ? 'EFECTIVO' : 'TARJETA'))->with('Estilo', 'alert alert-info');
        }

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
                if (count($listadoProductos) > 0 && $listadoProductos[0]->venta_por_unidad == 1) {
                    $peso = 1;
                    $cantidad = 1;
                } else {
                    $peso = (float)substr($request->codigo, 7, 6) / (10000 * (float)$divisor);
                    $cantidad = (float)substr($request->codigo, 7, 6) / (10000 * (float)$divisor);
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
                $peso = (float)substr($request->codigo, 7, 6) / (10000);
                $cantidad = (float)substr($request->codigo, 7, 6) / 10000;
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
            if (($xaccion == "addProducto" or $cantidadProdVentaTMP > 0) && $xaccion != "delProducto") {
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
                    $updateSql = "UPDATE " . $tableVentaDetalleTmp . " SET cantidad = " . $aaa . ",
                    precio_venta = " . $precio_venta . ",
                    sub_total =  " . $subTot . "
                    WHERE id_usuario = " . $id_usuario_actual . "
                    AND id_local = " . Auth::user()->id_local . "
                    AND substr(codigo,1,7) = '" . substr($request->codigo, 0, 7) . "'";
                    if ($folio_caja) {
                        $updateSql .= " AND folio_caja = '$folio_caja'";
                    } else {
                        $updateSql .= " AND estado_caja = 0";
                    }
                    $resultado = DB::select($updateSql . ";");
                } else {
                    $updateSql = "UPDATE " . $tableVentaDetalleTmp . " SET cantidad = " . $aaa . ",
                    precio_venta = " . $precio_venta . ",
                    sub_total =  " . $subTot . "
                    WHERE id_usuario = " . $id_usuario_actual . "
                    AND id_local = " . Auth::user()->id_local . "
                    AND trim(codigo) = '" . trim($request->codigo) . "'";
                    if ($folio_caja) {
                        $updateSql .= " AND folio_caja = '$folio_caja'";
                    } else {
                        $updateSql .= " AND estado_caja = 0";
                    }
                    $resultado = DB::select($updateSql . ";");
                }


                //dd($aaa,$precio_venta,$subTot,$resultado);
                $Mensaje = 'Producto Agregado !!!';
                $Estilo = 'alert alert-success';
            }

            if ($xaccion == "delProducto") {
                $aaa  = intval($cantidadProdVentaTMP) - 1;
                if ($aaa <= 0) {
                    $delSql = "DELETE FROM " . $tableVentaDetalleTmp . "
                        WHERE id_usuario = " . $id_usuario_actual . "
                        AND  id_local = " . Auth::user()->id_local . "
                        AND trim(codigo) = '" . trim($request->codigo) . "'";
                    if ($folio_caja) {
                        $delSql .= " AND folio_caja = '$folio_caja'";
                    } else {
                        $delSql .= " AND estado_caja = 0";
                    }
                    DB::select($delSql . ";");
                    $Mensaje = 'Producto Eliminado !!!';
                    $Estilo = 'alert alert-danger';
                } else {
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
                    $updSql = "UPDATE " . $tableVentaDetalleTmp . " SET cantidad = " . $aaa . " ,
                        precio_venta = " . $precio_venta . ",
                        sub_total = " . $subTot . "
                        WHERE id_usuario = " . $id_usuario_actual . "
                        AND  id_local = " . Auth::user()->id_local . "
                        AND trim(codigo) = '" . trim($request->codigo) . "'";
                    if ($folio_caja) {
                        $updSql .= " AND folio_caja = '$folio_caja'";
                    } else {
                        $updSql .= " AND estado_caja = 0";
                    }
                    DB::select($updSql . ";");
                    $Mensaje = 'Cantidad Rebajada !!!';
                    $Estilo = 'alert alert-warning';
                }
            }

            //dd($xaccion, $cantidadProdVentaTMP);
            if ($xaccion == "" and $cantidadProdVentaTMP == 0) {
                //echo "paso5";
                if (count($listadoProductos) > 0) {
                    DB::table($tableVentaDetalleTmp)->insert([
                        [
                            'id_local'      => Auth::user()->id_local,
                            'id_usuario'    => $id_usuario_actual,
                            'codigo'        => $codigo,
                            'empresa'       => $empresa,
                            'descripcion'   => $descripcion,
                            'fecha_venta'   => now(),
                            'precio_venta'  => $precio_venta,
                            'cantidad'      => $cantidad,
                            'stock'         => $cantidad_stock,
                            'sub_total'     => $precio_venta * $cantidad,
                            'estado_caja'   => $folio_caja ? 1 : 0,
                            'folio_caja'    => $folio_caja
                        ]
                    ]);
                } else {
                    $Mensaje = 'Producto no Encontrado!!!';
                    $Estilo = 'alert alert-danger';
                }
            }
            $listadoProductos = null;
        }

        $selTmp = "SELECT
        codigo,descripcion,
        CASE
            WHEN substring(CONVERT(SUM(cantidad) , CHAR), INSTR(CONVERT(SUM(cantidad) , CHAR) , '.')+1 ,4) = '0000' THEN substring(CONVERT(SUM(cantidad) , CHAR), 1, INSTR(CONVERT(SUM(cantidad) , CHAR) , '.')-1)
            ELSE CONVERT(SUM(cantidad) , CHAR)
        END AS cantidad,
        FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori , FORMAT((SUM(cantidad)*precio_venta),0,'es_CL') AS precio_venta,
        sum(stock) as stock
        FROM " . $tableVentaDetalleTmp . "
        WHERE id_usuario = " . $id_usuario_actual . "
        AND  id_local = " . Auth::user()->id_local . " ";
        if ($folio_caja) {
            $selTmp .= " AND folio_caja = '$folio_caja'";
        } else {
            $selTmp .= " AND estado_caja = 0";
        }
        $selTmp .= " GROUP BY codigo,descripcion,precio_venta ;";
        $ventaTmp = DB::select($selTmp);

        $selTot = "SELECT
        FORMAT(SUM(cantidad*precio_venta),0,'es_CL') AS total
        FROM " . $tableVentaDetalleTmp . "
        WHERE id_usuario = " . $id_usuario_actual . "
        AND  id_local = " . Auth::user()->id_local . " ";
        if ($folio_caja) {
            $selTot .= " AND folio_caja = '$folio_caja'";
        } else {
            $selTot .= " AND estado_caja = 0";
        }
        $totalTmp1 = DB::select($selTot . " ;");


        $totalTmp = $totalTmp1[0]->total;

        $totalDevolucion = (new DevolucionVentaController)->fncTraertotalDevolucion();

        $TotaFinal = number_format((int)((intval(str_replace('.', '', $totalTmp)) - intval(str_replace('.', '', $totalDevolucion)))), 0, '', '.');

        //$codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        //$codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

        //$buttonSize= (new ParametriaController)->fncButtonSize();
        //$textSize= (new ParametriaController)->fncTextoSize();
        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        $consultaPrecio = (new ParametriaController)->fncTraeConfiguracion('consultaPrecio');
        return redirect()->route('venta.index')->with('Mensaje', $Mensaje)->with('Estilo', $Estilo);
    }
    /////////////////////////////////////////////////////
    public function destroy(Request $request, $id)
    {
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $table = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $delSql = "DELETE FROM $table
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local . "
        AND codigo = '" . $id . "'";

        if ($folio_query) {
            $delSql .= " AND folio_caja = '$folio_query'";
        } else {
            $delSql .= " AND estado_caja = 0";
        }
        DB::select($delSql . ";");

        $selTmp = "SELECT
        codigo,descripcion,
        CASE
            WHEN substring(CONVERT(SUM(cantidad) , CHAR), INSTR(CONVERT(SUM(cantidad) , CHAR) , '.')+1 ,4) = '0000' THEN substring(CONVERT(SUM(cantidad) , CHAR), 1, INSTR(CONVERT(SUM(cantidad) , CHAR) , '.')-1)
            ELSE CONVERT(SUM(cantidad) , CHAR)
        END AS cantidad,
        FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori , FORMAT((SUM(cantidad)*precio_venta),0,'es_CL') AS precio_venta,
        sum(stock) as stock
        FROM " . $table . "
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local . " ";

        if ($folio_query) {
            $selTmp .= " AND folio_caja = '$folio_query'";
        } else {
            $selTmp .= " AND estado_caja = 0";
        }
        $selTmp .= " GROUP BY codigo,descripcion,precio_venta ;";
        $ventaTmp = DB::select($selTmp);

        $listadoProductos = $this->fncTraerProductos();

        $selTot = "SELECT
        FORMAT(SUM(cantidad*precio_venta),0,'es_CL') AS total
        FROM " . $table . "
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local . " ";

        if ($folio_query) {
            $selTot .= " AND folio_caja = '$folio_query'";
        } else {
            $selTot .= " AND estado_caja = 0";
        }
        $totalTmp1 = DB::select($selTot . " ;");
        $totalTmp = $totalTmp1[0]->total;

        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        $consultaPrecio = (new ParametriaController)->fncTraeConfiguracion('consultaPrecio');
        return redirect()->route('venta.index');
    }
    public function show($id)
    {
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableVtasDetalle = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $delSql = 'delete from ' . $tableVtasDetalle . ' where codigo = "' . $id . '" and id_local = ' . Auth::user()->id_local . ' and id_usuario =' . $id_query;
        if ($folio_query) {
            $delSql .= " AND folio_caja = '$folio_query'";
        } else {
            $delSql .= " AND estado_caja = 0";
        }
        DB::delete($delSql . ';');

        $selTmp = "SELECT
        codigo,descripcion, CAST(SUM(cantidad) AS UNSIGNED) as cantidad ,  FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori , FORMAT((SUM(cantidad)*precio_venta),0,'es_CL') AS precio_venta,  sum(stock) AS stock
        FROM " . $tableVtasDetalle . "
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local;

        if ($folio_query) {
            $selTmp .= " AND folio_caja = '$folio_query'";
        } else {
            $selTmp .= " AND estado_caja = 0";
        }
        $selTmp .= " GROUP BY codigo,descripcion,precio_venta;";
        $ventaTmp = DB::select($selTmp);

        $selTot = "SELECT
        FORMAT(SUM(cantidad*precio_venta),0,'es_CL') AS total
        FROM " . $tableVtasDetalle . " 
        WHERE id_usuario = " . $id_query . "
        AND  id_local = " . Auth::user()->id_local;

        if ($folio_query) {
            $selTot .= " AND folio_caja = '$folio_query'";
        } else {
            $selTot .= " AND estado_caja = 0";
        }
        $totalTmp1 = DB::select($selTot . ";");

        $listadoProductos = null;
        $totalTmp = $totalTmp1[0]->total;
        $totalDevolucion = (new DevolucionVentaController)->fncTraertotalDevolucion();

        $TotaFinal = number_format((int)((intval(str_replace('.', '', $totalTmp)) - intval(str_replace('.', '', $totalDevolucion)))), 0, '', '.');
        $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
        $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

        $cajaPagadora = (new ParametriaController)->fncTraeConfiguracion('cajaPagadora');
        $userNivel = Auth::user()->nivel;
        $consultaPrecio = (new ParametriaController)->fncTraeConfiguracion('consultaPrecio');
        return redirect()->route('venta.index');
    }
}
