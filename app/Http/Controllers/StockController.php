<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;

class StockController extends Controller
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
    public function fncTraeConfiguracion($texto)
    {
        $table = (new ParametriaController)->fncTraeTablaConfiguracion();
        $infParametros = DB::table($table)
            ->where('idLocal', '=', Auth::user()->id_local)
            ->where('categoria', $texto)
            ->get();
        return $infParametros[0]->valor;
    }

    public function fncTraeEmpresas()
    {
        $table = (new ParametriaController)->fncTraeTablaStock();
        $listEmpresas = DB::table($table)
            ->select('empresa')
            ->where('id_local', '=', Auth::user()->id_local)
            ->where('activo', '=', 1)
            ->orderBy('empresa', 'asc')
            ->distinct()
            ->get();
        return $listEmpresas;
    }

    public function fncTraerProductosId($texto)
    {
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $iva = $this->fncTraeConfiguracion('valorIVA');
        $listadoProductosStock = DB::select("SELECT
        id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
        case when activo = true then 1
             when activo = false then 0
        end as activo,
        precio_costo as precio_costo,
        precio_venta as precio_venta,
        (precio_costo - (precio_costo*$iva/100)) as precio_neto,
        precio_neto as precio_neto_ori,
        venta_por_unidad
        FROM " . $tableStock . "
        WHERE id_local = " . Auth::user()->id_local . "
        AND activo=1
        AND id_producto = $texto;");
        return $listadoProductosStock;
    }
    public function fncTraerProductos($texto)
    {

        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $iva = $this->fncTraeConfiguracion('valorIVA');
        if (strlen($texto) > 0) {
            $listadoProductosStock = DB::select("SELECT
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                 when activo = false then 0
            end as activo,
            precio_costo as precio_costo,
            precio_venta as precio_venta,
            (precio_costo - (precio_costo*$iva/100)) as precio_neto,
            precio_neto as precio_neto_ori,
            cantidad_venta_mayor,
            precio_venta_mayor as precio_venta_mayor,
            venta_por_unidad
            FROM " . $tableStock . "
            WHERE id_local = " . Auth::user()->id_local . "
            AND (
                   codigo LIKE '%$texto%'
                OR descripcion LIKE '%$texto%'
                OR empresa LIKE '%$texto%'
            )
            ORDER BY activo desc ,descripcion ASC
            LIMIT 500
            ;");
        } else {
            $listadoProductosStock = DB::select("SELECT
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                 when activo = false then 0
            end as activo,
            precio_costo as precio_costo ,
            precio_venta as precio_venta,
            (precio_costo - (precio_costo*$iva/100)) as precio_neto,
            precio_neto as precio_neto_ori,
            cantidad_venta_mayor,
            precio_venta_mayor as precio_venta_mayor,
            venta_por_unidad
            FROM    " . $tableStock . "
            WHERE   id_local = " . Auth::user()->id_local . "
            AND     (cantidad - cantidad_minima) <= 0
            AND     activo = true
            ORDER BY activo desc ,cantidad_minima ASC
            LIMIT 500
            ;");
        }
        return $listadoProductosStock;
    }

    public function fncDescripcion($codigo)
    {
        $tableMasterProductos = (new ParametriaController)->fncTraeTablaMaestraProductos();
        $descripcionProductos = DB::select("SELECT descripcion
            FROM " . $tableMasterProductos . "
            WHERE codigo ='" . $codigo . "';");

        if (count($descripcionProductos) == 0) {
            $desc = "";
        } else {
            $desc = $descripcionProductos[0]->descripcion;
        }

        return $desc;
    }

    public function fncInsertoMasterProductos($codigo, $descripcion)
    {
        $tableMasterProductos = (new ParametriaController)->fncTraeTablaMaestraProductos();
        $descripcionProductos = DB::select("SELECT descripcion
            FROM " . $tableMasterProductos . "
            WHERE codigo ='" . $codigo . "';");
        if (count($descripcionProductos) == 0) {
            DB::table($tableMasterProductos)
                ->insert(
                    [
                        'codigo'          => $codigo,
                        'descripcion'     => $descripcion
                    ]
                );
        }
        if (count($descripcionProductos) == 1 and strlen($descripcionProductos[0]->descripcion) == 0) {
            DB::table($tableMasterProductos)
                ->where('codigo', '=', $codigo)
                ->update(
                    [
                        'descripcion'     => $descripcion
                    ]
                );
        }
    }

    public function fncCantidadProducto($codigo)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $cantidad = DB::table($tableStock)
            ->where('codigo', '=', $codigo)
            ->where('id_local', '=', Auth::user()->id_local)
            ->count();
        return $cantidad;
    }
    ///////////////////////////////////
    public function index(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $Mensaje = null;
        $Estilo = null;
        $codigo = trim($request->get('codigo'));
        $listadoProductosStock = $this->fncTraerProductos($codigo);
        $listaEmpresas = $this->fncTraeEmpresas();
        if (strlen($codigo) > 3) {

            if (count($listadoProductosStock) == 0) {
                $Mensaje = 'Producto no Encontrado!!!';
                $Estilo = 'alert alert-danger';
                $iva = $this->fncTraeConfiguracion('valorIVA');
                $ganacia = $this->fncTraeConfiguracion('porcentajeGanacia');
                $listaEmpresas = $this->fncTraeEmpresas();
                $descripcionPorducto = $this->fncDescripcion($codigo);
                return view('Stock.create', compact('iva', 'ganacia', 'listaEmpresas', 'descripcionPorducto', 'codigo'));
            }
            if (count($listadoProductosStock) == 1) {
                $this->edit($listadoProductosStock[0]->codigo);
            }
        }

        if (count($listadoProductosStock) == 0) {
            $Mensaje = 'Producto no Encontrado!!!';
            $Estilo = 'alert alert-danger';
        }

        return view('Stock.index', compact('listadoProductosStock', 'listaEmpresas', 'Mensaje', 'Estilo', 'codigo'));
    }
    public function create()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $iva = $this->fncTraeConfiguracion('valorIVA');
        $ganacia = $this->fncTraeConfiguracion('porcentajeGanacia');
        $listaEmpresas = $this->fncTraeEmpresas();
        $descripcionPorducto = null;
        $codigo = null;
        return view('Stock.create', compact('iva', 'ganacia', 'listaEmpresas', 'descripcionPorducto', 'codigo'));
    }


    public function store(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $codigo = $request->codigo;
        $table = (new ParametriaController)->fncTraeTablaStock();
        $tableLog = (new ParametriaController)->fncTraeTablaLog();
        $cantidad = $this->fncCantidadProducto($codigo);
        if ($cantidad == 0) {
            DB::table($table)
                ->insert(
                    [
                        'id_local'        => Auth::user()->id_local,
                        'activo'          => 1,
                        'empresa'         => $request->empresa,
                        'codigo'          => $request->codigo,
                        'descripcion'     => $request->descripcion,
                        'precio_neto'     => str_replace(".", "", $request->precio_neto),
                        'precio_costo'    => str_replace(".", "", $request->precio_costo),
                        'precio_venta'    => str_replace(".", "", $request->precio_venta),
                        'cantidad'        => $request->cantidad,
                        'cantidad_minima' => $request->cantidad_minima,
                        'venta_por_unidad' => $request->venta_por_unidad,
                        'ultima_actualizacion' => now(),
                        'actualizacion_estado' => 'pendiente'
                    ]
                );

            DB::table($tableLog)
                ->insert(
                    [
                        'id_local'      => Auth::user()->id_local,
                        'id_usuario'    => Auth::user()->id_usuario,
                        'fecha'         => now(),
                        'descripcion'   => 'Ingreso Producto Codigo:' . $request->codigo . ' Usr:' . Auth::user()->id_usuario . ' Local:' . Auth::user()->id_local . ' precio_costo:' . str_replace(".", "", $request->precio_costo) . ' precio_venta :' . str_replace(".", "", $request->precio_venta) . ' Cantidad:' . $request->cantidad,
                        'ultima_actualizacion' => now(),
                        'actualizacion_estado' => 'pendiente'
                    ]
                );
        }
        // inserto en la master de productos
        if (strlen($request->codigo) >= 10) {
            $this->fncInsertoMasterProductos($request->codigo, $request->descripcion);
        }
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $iva = $this->fncTraeConfiguracion('valorIVA');
        $ganacia = $this->fncTraeConfiguracion('porcentajeGanacia');

        $ProductoEditar =
            DB::select("SELECT
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                 when activo = false then 0
            end as activo,
            precio_costo as precio_costo ,
            precio_venta as precio_venta,
            (precio_costo - (precio_costo*$iva/100)) as precio_neto,
            precio_neto as precio_neto_ori,
            cantidad_venta_mayor,
            precio_venta_mayor as precio_venta_mayor,
            venta_por_unidad
            FROM " . $tableStock . "
            WHERE id_local = " . Auth::user()->id_local . "
            AND codigo = $request->codigo;");

        $listaEmpresas = $this->fncTraeEmpresas();

        //return view('Stock.edit', compact('ProductoEditar','iva','ganacia','listaEmpresas'));

        $listadoProductosStock = $this->fncTraerProductos($request->codigo);
        return view('Stock.index', compact('listadoProductosStock', 'listaEmpresas', 'codigo'));
    }

    public function edit($idProducto)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $iva = $this->fncTraeConfiguracion('valorIVA');
        $ganacia = $this->fncTraeConfiguracion('porcentajeGanacia');

        $ProductoEditar =
            DB::select("SELECT
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                 when activo = false then 0
            end as activo,
            precio_costo as precio_costo ,
            precio_venta as precio_venta,
            (precio_costo - (precio_costo*$iva/100)) as precio_neto,
            precio_neto as precio_neto_ori,
            cantidad_venta_mayor,
            precio_venta_mayor as precio_venta_mayor,
            venta_por_unidad
            FROM " . $tableStock . "
            WHERE id_local = " . Auth::user()->id_local . "
            AND id_producto = $idProducto;");

        $listaEmpresas = $this->fncTraeEmpresas();

        return view('Stock.edit', compact('ProductoEditar', 'listaEmpresas', 'iva', 'ganacia'));
    }
    public function update(Request $request, $id)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $codigo = $request->codigo;
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        $activar = false;
        if (intval($request->cmbActivo) == 1) {
            $activar = true;
        }

        DB::table($tableStock)
            ->where('id_producto', '=', $id)
            ->update(
                [
                    'codigo'          => $codigo,
                    'empresa'         => $request->empresa,
                    'activo'          => $activar,
                    'descripcion'     => $request->descripcion,
                    'precio_neto'     => str_replace(".", "", $request->precio_neto),
                    'precio_costo'    => str_replace(".", "", $request->precio_costo),
                    'precio_venta'    => str_replace(".", "", $request->precio_venta),
                    'cantidad'        => $request->cantidad,
                    'activo'          => 1,
                    'cantidad_minima' => $request->cantidad_minima,
                    'cantidad_venta_mayor' => $request->cantidad_venta_mayor,
                    'precio_venta_mayor' => str_replace(".", "", $request->precio_venta_mayor),
                    'venta_por_unidad' => $request->venta_por_unidad,
                    'ultima_actualizacion' => now(),
                    'actualizacion_estado' => 'pendiente'
                ]
            );


        $listadoProductosStock = $this->fncTraerProductos($codigo);

        $listaEmpresas = $this->fncTraeEmpresas();
        $Mensaje = 'Producto Actualizado Correctamente!!!';
        $Estilo = 'alert alert-success';

        return view('Stock.index', compact('listadoProductosStock', 'listaEmpresas', 'codigo'));
    }
    public function destroy(Request $request, $id)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $codigo = $request->codigo;
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        DB::table($tableStock)
            ->where('id_producto', '=', $id)
            ->update(
                [
                    'activo'          => false,
                    'ultima_actualizacion' => now(),
                    'actualizacion_estado' => 'pendiente'
                ]
            );
        $listadoProductosStock = $this->fncTraerProductos($codigo);
        $listaEmpresas = $this->fncTraeEmpresas();
        $Mensaje = 'Producto Actualizado Correctamente!!!';
        $Estilo = 'alert alert-success';

        return view('Stock.index', compact('listadoProductosStock', 'listaEmpresas', 'codigo'));
    }
    public function show(Request $request, $id)
    {
        $parametros = explode("|", str_replace("%7C", "|", $request->path()));
        $idProducto = $parametros[1];
        $accion = $parametros[2];
        $cantidad = $parametros[3];
        if ($accion == 'cambiastock') {
            $tableStock = (new ParametriaController)->fncTraeTablaStock();
            DB::table($tableStock)
                ->where('id_producto', '=', $idProducto)
                ->update(
                    [
                        'cantidad'          => $cantidad,
                        'ultima_actualizacion' => now(),
                        'actualizacion_estado' => 'pendiente'
                    ]
                );
        }
        $listadoProducto = $this->fncTraerProductosId($idProducto);
        $codigo = $listadoProducto[0]->empresa;
        $listadoProductosStock = $this->fncTraerProductos($codigo);
        $listaEmpresas = $this->fncTraeEmpresas();

        $Mensaje = 'Producto Actualizado Correctamente!!!';
        $Estilo = 'alert alert-success';

        return view('Stock.index', compact('listadoProductosStock', 'listaEmpresas', 'codigo'));
    }
}
