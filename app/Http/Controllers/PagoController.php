<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PagoController extends Controller
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
    public function fncFormatoMoneda($valor)
    {
        $aa = 0;
        if (strlen($valor) == 3) {
            $aa = $valor;
        }
        if (strlen($valor) == 4) {
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
    public function fncDevolucionAddProductos()
    {
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $listaProducoDevolucion =  (new DevolucionVentaController)->fncListadoProductoDevolucion();
        foreach ($listaProducoDevolucion as $LsProdDev) {
            $cantidadStock = (new StockController)->fncTraeProductoPorCodigo($LsProdDev->codigo)[0]->cantidad;
            $cantidadNew = $cantidadStock + intval($LsProdDev->cantidad);
            $formatedDate = Carbon::now()->format('Y-m-d');
            // ACTUALIZO EL STOCK
            DB::table($tableStock)
                ->where('codigo', $LsProdDev->codigo)
                ->update([
                    'cantidad' => $cantidadNew,
                    'ultima_actualizacion' => now(),
                    'actualizacion_estado' => 'pendiente'
                ]);
        }
    }
    public function fncSocktDescontarProductos()
    {
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $listadoVentasTmp = (new VentaController)->fncTraerlistaVentasTmp();

        foreach ($listadoVentasTmp as $LsProdVentas) {
            //CODIGO
            if (substr($LsProdVentas->codigo, 0, 3) == "210") {
                $ventaGramo = true;
                $codigo   = substr($LsProdVentas->codigo, 0, 7);
                $peso     = substr($LsProdVentas->codigo, 7, 6) / 10000;
                //CANTIDAD DEL STOCK
                $cantidadStock = (new StockController)->fncTraeProductoPorCodigo($codigo)[0]->cantidad;
            } else {
                $cantidadStock = (new StockController)->fncTraeProductoPorCodigo($LsProdVentas->codigo)[0]->cantidad;
            }
            //CANTIDAD NUEVA (DESCUENTO LOS PRODUCTOS)
            if (isset($ventaGramo)) {
                $cantidadNew = $cantidadStock - $peso;
            } else {
                $cantidadNew = $cantidadStock - intval($LsProdVentas->cantidad);
            }
            $formatedDate = Carbon::now()->format('Y-m-d');
            // ACTUALIZO EL STOCK
            DB::table($tableStock)
                ->where('codigo', $LsProdVentas->codigo)
                ->update([
                    'cantidad' => $cantidadNew,
                    'ultima_actualizacion' => now(),
                    'actualizacion_estado' => 'pendiente'
                ]);
        }
    }

    public function fncInsertoBorroTablaDevolucion()
    {
        $tableDevolucionFinal = (new ParametriaController)->fncTraeTablaDevolucionDetalleFinal();
        $tableDevolucionTmp = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();
        // INSERTO EN y BORRO LA TABLA FINAL DE DEVOLUCION
        DB::select("INSERT INTO $tableDevolucionFinal SELECT * FROM $tableDevolucionTmp
        WHERE id_local = " . Auth::user()->id_local . "
        AND id_usuario= " . Auth::user()->id_usuario . "");

        // BORRO DE LA TABLA DE DEVOLUCION TEMPORAL
        DB::select("DELETE FROM $tableDevolucionTmp
        WHERE id_local = " . Auth::user()->id_local . "
        AND id_usuario= " . Auth::user()->id_usuario . "");
    }


    public function fncActualizarTotalVentas($formaPago)
    {
        $tableVentasTotal = (new ParametriaController)->fncTraeTablaVentasTotales();
        $tableVentas = (new ParametriaController)->fncTraeTablaVentas();
        $tableVentasTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();
        $tableDevolucion = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();

        $totalDevolucion = str_replace(".", "", (new DevolucionVentaController)->fncTraertotalDevolucion());
        $totalVentaTmp = (new VentaController)->fncTraerTotalVentaTmp();

        // inserto la devolucion
        DB::select("INSERT INTO $tableVentasTotal
            (
                id_local,
                id_usuario,
                fecha_venta,
                total_venta,
                forma_pago,
                monto_sencillo,
                vuleto,
                ultima_actualizacion,
                actualizacion_estado
            )
            VALUES
            (
                " . Auth::user()->id_local . ",
                " . Auth::user()->id_usuario . ",
                now(),
                -" . $totalDevolucion . ",
                2,
                0,
                0,
                NOW(),
                'pendiente'
            )
            ");

        $maxIdDevolucion = DB::select("SELECT MAX(id_ventas) as maxIdVenta
        FROM $tableVentasTotal
        WHERE id_usuario = " . Auth::user()->id_usuario . " AND id_local = " . Auth::user()->id_local . ";");
        DB::select("INSERT INTO $tableVentas (
            id_ventas_total,
            id_local,
            id_usuario,
            codigo,
            empresa,
            descripcion,
            fecha_venta,
            forma_pago,
            precio_venta,
            cantidad,
            sub_total,
            ultima_actualizacion,
            actualizacion_estado)
        select
            " . $maxIdDevolucion[0]->maxIdVenta . ",
            id_local,
            id_usuario,
            codigo,
            empresa,
            descripcion,
            NOW(),
            " . $formaPago . ",
            precio_venta,
            cantidad,
            -(precio_venta*cantidad),
            NOW(),
            'pendiente'
        FROM $tableDevolucion
        WHERE id_usuario = " . Auth::user()->id_usuario . " AND id_local = " . Auth::user()->id_local . ";");


        // inserto la ventatotal
        DB::select("INSERT INTO $tableVentasTotal
        (
            id_local,
            id_usuario,
            fecha_venta,
            total_venta,
            forma_pago,
            monto_sencillo,
            vuleto,
            ultima_actualizacion,
            actualizacion_estado
        )
        VALUES
        (
            " . Auth::user()->id_local . ",
            " . Auth::user()->id_usuario . ",
            now(),
            " . $totalVentaTmp . ",
            2,
            0,
            0,
            NOW(),
            'pendiente'
        )
        ");

        $maxIdVenta = DB::select("SELECT MAX(id_ventas) as maxIdVenta
        FROM $tableVentasTotal
        WHERE id_usuario = " . Auth::user()->id_usuario . " AND id_local = " . Auth::user()->id_local . ";");
        DB::select("INSERT INTO $tableVentas (
            id_ventas_total,
            id_local,
            id_usuario,
            codigo,
            empresa,
            descripcion,
            fecha_venta,
            forma_pago,
            precio_venta,
            cantidad,
            sub_total,
            ultima_actualizacion,
            actualizacion_estado)
        select
            " . $maxIdVenta[0]->maxIdVenta . ",
            id_local,
            id_usuario,
            codigo,
            empresa,
            descripcion,
            NOW(),
            " . $formaPago . ",
            precio_venta,
            cantidad,
            (precio_venta*cantidad),
            NOW(),
            'pendiente'
        FROM $tableVentasTmp
        WHERE id_usuario = " . Auth::user()->id_usuario . " AND id_local = " . Auth::user()->id_local . ";");
    }

    public function fncDevolucion($formaPago)
    {

        $totalDevolucion = str_replace(".", "", (new DevolucionVentaController)->fncTraertotalDevolucion());
        $ValorTotal = intval($this->fncTraeventasTotal()[0]->ValorTotal);

        // SI HAY DEVOLUCION Y NO HAY VENTA
        if ($ValorTotal == 0 and $totalDevolucion > 0) {
            // AGREGAR AL STOCK LOS PRODUCTOS DEVUELTOS
            $this->fncDevolucionAddProductos();

            // DESCONTAR DEL TOTAL
            $this->fncActualizarTotalVentas($formaPago);

            // ACTUALIZAR TABLA CAJA
            if ($formaPago == 2) {
                (new CajaController)->fncCajaRegistraMovimiento(- ($totalDevolucion));
            }
        }

        if ($ValorTotal > 0 and $totalDevolucion > 0) {

            // AGREGAR AL STOCK LOS PRODUCTOS DEVUELTOS
            $this->fncDevolucionAddProductos();
            // QUITAR  DEL STOCK LOS PRODUCTOS VENDIDOS
            $this->fncSocktDescontarProductos();
            // ACTUALIZAR EL TOTAL_VENTAS
            $this->fncActualizarTotalVentas($formaPago);
            // ACTUALIZAR TABLA CAJA - TOTAL_DEVOLUCION
            $calculoFinal = $ValorTotal - $totalDevolucion;
            if ($formaPago == 2) {

                if ($calculoFinal < 0) {
                    // DEVOLVER AL CLIENTE
                    (new CajaController)->fncCajaRegistraMovimiento($calculoFinal);
                } else {

                    // CLIENTE PAGA
                    (new CajaController)->fncCajaRegistraMovimiento($calculoFinal);
                }
            }
        }
        // INSERTO EN y BORRO LA TABLA FINAL DE DEVOLUCION
        $this->fncInsertoBorroTablaDevolucion();
    }


    public function fncTraeConfiguracion($texto)
    {
        $table = (new ParametriaController)->fncTraeTablaConfiguracion();
        $infParametros = DB::table($table)
            ->where('idLocal', '=', Auth::user()->id_local)
            ->where('categoria', $texto)
            ->get();
        return $infParametros[0]->valor;
    }

    public function fncTraeVentasResumen()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "SELECT codigo,
        descripcion,
        FORMAT(precio_venta,0,'es_CL') as precio_venta,forma_pago,
        DATE_FORMAT(fecha_venta, '%Y%m%d') AS fecha_venta,
        CASE
            WHEN substring(CONVERT(cantidad , CHAR), INSTR(CONVERT(cantidad , CHAR) , '.')+1 ,4) = '0000' THEN substring(CONVERT(cantidad , CHAR), 1, INSTR(CONVERT(cantidad , CHAR) , '.')-1)
            ELSE CONVERT(cantidad , CHAR)
        END AS cantidad,
        FORMAT(sum(precio_venta*cantidad),0,'es_CL') AS ValorTotal
            FROM $tableTmp
            WHERE id_usuario = $id_query
            AND id_local = " . Auth::user()->id_local;

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        $sql .= " GROUP BY codigo,descripcion,DATE_FORMAT(fecha_venta, '%Y%m%d'),precio_venta,forma_pago ,cantidad";

        return DB::select($sql);
    }
    public function fncTraeventasTotal()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "SELECT FLOOR(sum(cantidad*precio_venta)) AS ValorTotal
            FROM $tableTmp
            WHERE id_usuario = $id_query
            AND id_local     = " . Auth::user()->id_local;

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        return DB::select($sql);
    }

    public function index(Request $request)
    {
        dd("index ", $request);
    }
    public function update(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }

        $totalDevolucion = str_replace(".", "", (new DevolucionVentaController)->fncTraertotalDevolucion());

        // PROCESO DEVOLUCION
        if (intval($totalDevolucion) > 0) {
            $this->fncDevolucion($request->formaPago);
            if ($request->formaPago == 1 || $request->formaPago == 3) {
                $Mensaje = 'Pago Ingresado con Tarjeta o Transferencia Correctamente (' . $request->totalPagar . ')!!!';
            } else {
                $montoSencillo = $this->fncFormatoMoneda($request->montoSencillo);
                $totalVuelto = $this->fncFormatoMoneda($request->totalVuelto);
                $Mensaje = 'Pago en efectivo Ingresado Correctamente (Monto:' . $request->totalPagar . ' Pago:' . $montoSencillo . ' Vuelto:' . $totalVuelto . ')!!!';
            }
        } else {
            $id_vendedor = session('vendedor_id_original', Auth::user()->id_usuario);
            $folio_caja = session('folio_caja');
            $tableVentasDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

            // Verificar si hay ventas temporales antes de procesar
            $queryCheck = DB::table($tableVentasDetalleTmp)
                ->where('id_usuario', $id_vendedor)
                ->where('id_local', Auth::user()->id_local);

            if ($folio_caja) {
                $queryCheck->where('folio_caja', $folio_caja);
            } else {
                $queryCheck->where('estado_caja', 0);
            }

            if (!$queryCheck->exists()) {
                return redirect()->route('venta.index')->with(['Mensaje' => 'Esta venta ya fue procesada o está vacía.', 'Estilo' => 'alert alert-warning']);
            }

            if ($request->formaPago == 1 || $request->formaPago == 3) {
                $resultado = $this->fncGuardaVenta(
                    Auth::user()->id_local,
                    $id_vendedor,
                    str_replace(".", "", $request->totalPagar),
                    str_replace(".", "", $request->totalPagar),
                    $request->formaPago,
                    0,
                    $folio_caja
                );
                $Mensaje = 'Pago Ingresado con Tarjeta o Transferencia Correctamente (' . $request->totalPagar . ')!!!';
            } else {
                $montoSencillo = $this->fncFormatoMoneda($request->montoSencillo);
                $totalVuelto = $this->fncFormatoMoneda($request->totalVuelto);
                $resultado = $this->fncGuardaVenta(
                    Auth::user()->id_local,
                    $id_vendedor,
                    $request->montoSencillo,
                    str_replace(".", "", $request->totalPagar),
                    $request->formaPago,
                    $request->totalVuelto,
                    $folio_caja
                );
                $Mensaje = 'Pago en efectivo Ingresado Correctamente (Monto:' . $request->totalPagar . ' Pago:' . $montoSencillo . ' Vuelto:' . $totalVuelto . ')!!!';
            }

            if (!$resultado['success']) {
                $Mensaje = 'Error: ' . $resultado['message'];
                return redirect()->route('venta.index')->with(['Mensaje' => $Mensaje, 'Estilo' => 'alert alert-danger']);
            }
        }

        $Estilo = 'alert alert-success';

        $verResumenCaja = $this->fncTraeConfiguracion('verResumenCaja');
        if ($verResumenCaja == true) {
            $valorEnCaja = (new CajaController)->fncCajaTraeMonto();
            $Mensaje .= " Monto en Efectivo caja (" . $valorEnCaja . ")";
        }

        // Limpiar sesión de folio
        session()->forget(['folio_caja', 'vendedor_id_original']);

        if (Auth::user()->nivel >= 10) {
            return redirect()->route('Caja.index')->with(['Mensaje' => $Mensaje, 'Estilo' => $Estilo]);
        }
        return redirect()->route('venta.index')->with(['Mensaje' => $Mensaje, 'Estilo' => $Estilo]);
    }

    public function store(Request $request)
    {
        // vengo del index de ventas
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $VentasDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $updateQuery = DB::table($VentasDetalleTmp)
            ->where('id_usuario', '=', $id_query)
            ->where('id_local', '=', Auth::user()->id_local);

        if ($folio_query) {
            $updateQuery->where('folio_caja', $folio_query);
        } else {
            $updateQuery->where('estado_caja', 0);
        }

        $updateQuery->update(
            [
                'forma_pago'  => $request->formaPago,
                'ultima_actualizacion' => now(),
                'actualizacion_estado' => 'pendiente'
            ]
        );

        $ventasTmp = $this->fncTraeVentasResumen();

        $formaPago = $request->formaPago;
        $ValorTotal = intval($this->fncTraeventasTotal()[0]->ValorTotal);

        $totalDevolucion = (new DevolucionVentaController)->fncTraertotalDevolucion();

        $mensajeFinal = '';
        // SI HAY DEVOLUCION SE REALIZA EL CALCULO
        if ($totalDevolucion > 0) {
            $listaDevolucion = (new DevolucionVentaController)->fncListadoProductoDevolucion();
            $intDev   = intval(str_replace('.', '', $totalDevolucion));
            $intVlTot = intval(str_replace('.', '', $ValorTotal));
            $totalTmp = (new VentaController)->fncFormatoMoneda($intVlTot - $intDev);

            if ($intDev > $intVlTot) {
                $mensajeFinal = "Devolver al Cliente";
            } else {
                $mensajeFinal = "Total Venta";
            }
            return view('Pagos.index', compact('ventasTmp', 'totalTmp', 'formaPago', 'mensajeFinal', 'listaDevolucion'));
        }


        if ($totalDevolucion <= 0) {
            /*            $listadoProductos = null;
            $ventaTmp=null;
            $totalTmp=null;
            return view('Ventas.index', compact('listadoProductos','ventaTmp','totalTmp'));
            */
            $codBarraEfectivo = (new ParametriaController)->fncTraeConfiguracion('codBarraEfectivo');
            $codBarraTarjeta = (new ParametriaController)->fncTraeConfiguracion('codBarraTarjeta');

            $totalTmp = (new VentaController)->fncFormatoMoneda(intval($this->fncTraeventasTotal()[0]->ValorTotal));
            $listaDevolucion = null;
            return view('Pagos.index', compact('ventasTmp', 'totalTmp', 'formaPago', 'mensajeFinal', 'listaDevolucion', 'codBarraEfectivo', 'codBarraTarjeta'));
        }
    }
    public function destroy(Request $request)
    {
        dd("destroyd", $request);
    }
    //public function show(Request $request)
    public function show($Codigo)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        $id_query = session('vendedor_id_original', Auth::user()->id_usuario);
        $folio_query = session('folio_caja');
        $tableTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        $sql = "DELETE FROM $tableTmp 
                WHERE id_local = " . Auth::user()->id_local . " 
                AND id_usuario = $id_query 
                AND codigo = '$Codigo'";

        if ($folio_query) {
            $sql .= " AND folio_caja = '$folio_query'";
        } else {
            $sql .= " AND estado_caja = 0";
        }

        DB::delete($sql);

        $ventasTmp = $this->fncTraeVentasResumen();
        $totalTmp = $this->fncTraeventasTotal()[0]->ValorTotal;
        $formaPago = !empty($ventasTmp) ? $ventasTmp[0]->forma_pago : 2; // Default a efectivo si no hay ventas
        return view('Pagos.index', compact('ventasTmp', 'totalTmp', 'formaPago'));
    }
    /**
     * Guarda la venta finalizando el proceso.
     * Utiliza bloqueos a nivel de base de datos (MySQL Advisory Locks) para serializar
     * las solicitudes por usuario y evitar registros duplicados por concurrencia.
     */
    public function fncGuardaVenta($v_idLocal, $v_idUsuario, $v_MontoSencillo, $v_total_venta, $v_forma_pago, $v_vuleto, $v_folio = null)
    {
        // Nombre de bloqueo único por local y usuario para serializar el proceso
        $lockName = "guarda_venta_lock_{$v_idLocal}_{$v_idUsuario}";

        try {
            // Intentar obtener el bloqueo (esperar hasta 10 segundos)
            $lockAcquired = DB::select("SELECT GET_LOCK(?, 10) as locked", [$lockName]);

            if (!$lockAcquired || $lockAcquired[0]->locked != 1) {
                return [
                    'success' => false,
                    'message' => 'El sistema está procesando otra solicitud para este usuario. Por favor, reintente en unos segundos.'
                ];
            }

            // Variables iniciales
            $V_ID_VENTAS = 0;
            $V_CODIGO = '';
            $V_CANTIDAD = 0;
            $V_CANTIDAD_OK = 0;
            $Q_ID_DATOS_CAJA = 0;
            $V_MONTO_CAJA_PARAMETRIA = 0;
            $V_MONTO_CAJA_INI = 0;
            $V_MONTO_CAJA_NEW = 0;
            $V_MONTO_CAJA_FINAL = 0;

            // Obtener tablas
            $tableLog = (new ParametriaController)->fncTraeTablaLog();
            $tableVentasDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();
            $tableVentasTotal = (new ParametriaController)->fncTraeTablaVentasTotales();
            $tableVentas = (new ParametriaController)->fncTraeTablaVentas();
            $tableStock = (new ParametriaController)->fncTraeTablaStock();
            $tableCaja = (new ParametriaController)->fncTraeTablaCaja();
            $tableConfiguracion = (new ParametriaController)->fncTraeTablaConfiguracion();

            DB::beginTransaction();

            $ahora = now();

            // Verificar si hay ventas temporales (con bloqueo de lectura para asegurar consistencia)
            $queryVentas = DB::table($tableVentasDetalleTmp)
                ->where('id_usuario', $v_idUsuario)
                ->where('id_local', $v_idLocal);

            if ($v_folio) {
                $queryVentas->where('folio_caja', $v_folio);
            } else {
                $queryVentas->where('estado_caja', 0);
            }

            $ventasTmp = $queryVentas->lockForUpdate()->get();
            $Q_VENTAS = $ventasTmp->count();

            if ($Q_VENTAS == 0) {
                DB::rollBack();
                DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);
                return [
                    'success' => false,
                    'message' => 'No hay productos para procesar. Es posible que la venta ya haya sido guardada desde otra pestaña.'
                ];
            }

            // Log inicio proceso
            DB::table($tableLog)->insert([
                'id_local' => $v_idLocal,
                'id_usuario' => Auth::user()->id_usuario,
                'fecha' => $ahora,
                'descripcion' => "--->Inicio proceso cierre Ventas (Vendedor: $v_idUsuario, Folio: " . ($v_folio ?? 'N/A') . ")",
                'ultima_actualizacion' => $ahora,
                'actualizacion_estado' => 'pendiente'
            ]);

            // Insertar en ventas_total
            // El índice UNIQUE idx_prevent_duplicates garantiza que no haya inserciones idénticas simultáneas
            try {
                $ventaTotalId = DB::table($tableVentasTotal)->insertGetId([
                    'id_usuario' => $v_idUsuario,
                    'id_local' => $v_idLocal,
                    'monto_sencillo' => $v_MontoSencillo,
                    'total_venta' => $v_total_venta,
                    'forma_pago' => $v_forma_pago,
                    'vuleto' => $v_vuleto,
                    'fecha_venta' => $ahora,
                    'ultima_actualizacion' => $ahora,
                    'actualizacion_estado' => 'pendiente'
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Manejar error de duplicado (Código SQL 23000)
                if ($e->getCode() == '23000') {
                    DB::rollBack();
                    DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);
                    return [
                        'success' => false,
                        'message' => 'Esta venta ya ha sido registrada (Duplicado detectado).'
                    ];
                }
                throw $e;
            }

            $V_ID_VENTAS = $ventaTotalId;

            // Insertar detalles
            foreach ($ventasTmp as $venta) {
                DB::table($tableVentas)->insert([
                    'id_ventas_total' => $V_ID_VENTAS,
                    'id_local' => $venta->id_local,
                    'id_usuario' => $venta->id_usuario,
                    'codigo' => $venta->codigo,
                    'empresa' => $venta->empresa,
                    'descripcion' => substr($venta->descripcion, 0, 100),
                    'fecha_venta' => $ahora,
                    'forma_pago' => $v_forma_pago,
                    'precio_venta' => $venta->precio_venta,
                    'cantidad' => $venta->cantidad,
                    'sub_total' => ($venta->precio_venta * $venta->cantidad),
                    'ultima_actualizacion' => $ahora,
                    'actualizacion_estado' => 'pendiente'
                ]);
            }

            // Descuento stock
            foreach ($ventasTmp as $producto) {
                $V_CODIGO = $producto->codigo;
                $V_CANTIDAD = $producto->cantidad;
                $V_CANTIDAD_OK = (fmod($V_CANTIDAD, 1) == 0) ? $V_CANTIDAD : ($V_CANTIDAD * 1000);

                DB::table($tableStock)
                    ->where('codigo', $V_CODIGO)
                    ->where('id_local', $v_idLocal)
                    ->update([
                        'cantidad' => DB::raw('cantidad - ' . $V_CANTIDAD_OK),
                        'ultima_actualizacion' => $ahora,
                        'actualizacion_estado' => 'pendiente'
                    ]);
            }

            // Borrar de temporales
            $queryVentas->delete();

            // Proceso caja
            $Q_ID_DATOS_CAJA = DB::table($tableCaja)
                ->where('id_local', $v_idLocal)
                ->whereDate('fecha_movimiento', $ahora->format('Y-m-d'))
                ->max('id_caja');

            $configCaja = DB::table($tableConfiguracion)
                ->where('categoria', 'dineroInicialCaja')
                ->where('idLocal', $v_idLocal)
                ->first();

            $V_MONTO_CAJA_PARAMETRIA = $configCaja ? intval($configCaja->valor) : 0;

            if (!$Q_ID_DATOS_CAJA) {
                DB::table($tableCaja)->insert([
                    'id_local' => $v_idLocal,
                    'fecha_movimiento' => $ahora->format('Y-m-d'),
                    'monto' => $V_MONTO_CAJA_PARAMETRIA,
                    'ultima_actualizacion' => $ahora,
                    'actualizacion_estado' => 'pendiente'
                ]);
            }

            $V_MONTO_CAJA_NEW = DB::table($tableVentasTotal)
                ->where('id_local', $v_idLocal)
                ->where('forma_pago', 2)
                ->whereDate('fecha_venta', $ahora->format('Y-m-d'))
                ->sum('total_venta');

            $V_MONTO_CAJA_FINAL = $V_MONTO_CAJA_PARAMETRIA + ($V_MONTO_CAJA_NEW ?: 0);

            DB::table($tableCaja)
                ->where('id_local', $v_idLocal)
                ->whereDate('fecha_movimiento', $ahora->format('Y-m-d'))
                ->update([
                    'monto' => $V_MONTO_CAJA_FINAL,
                    'fecha_actualizacion' => $ahora,
                    'ultima_actualizacion' => $ahora,
                    'actualizacion_estado' => 'pendiente'
                ]);

            DB::table($tableLog)->insert([
                'id_local' => $v_idLocal,
                'id_usuario' => $v_idUsuario,
                'fecha' => now(),
                'descripcion' => "fin proceso venta (ID_TOTAL: $V_ID_VENTAS)",
                'ultima_actualizacion' => now(),
                'actualizacion_estado' => 'pendiente'
            ]);

            DB::commit();
            DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);

            return [
                'success' => true,
                'message' => 'Venta guardada correctamente',
                'id_venta' => $V_ID_VENTAS
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            DB::select("SELECT RELEASE_LOCK(?)", [$lockName]);

            // Log del error para depuración
            \Illuminate\Support\Facades\Log::error('Error en fncGuardaVenta: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());

            return [
                'success' => false,
                'message' => 'Error al guardar la venta: ' . $e->getMessage()
            ];
        }
    }
}
