<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;


class StockPackController extends Controller
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
    public function fncTraerProductosActivos($texto)
    {
        $tableStock = (new ParametriaController)->fncTraeTablaStock();
        if (strlen($texto) > 0) {
            $listadoProductosStock = DB::select("SELECT 
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                 when activo = false then 0
            end as activo,
            precio_costo as precio_costo,  
            precio_venta as precio_venta
            FROM " . $tableStock . "
            WHERE id_local = " . Auth::user()->id_local . "

            AND (codigo LIKE '%$texto%'
            OR descripcion LIKE '%$texto%'
            OR empresa LIKE '%$texto%'
            )
            ORDER BY activo desc ,descripcion ASC;");
        } else {
            $listadoProductosStock = DB::select("SELECT 
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                 when activo = false then 0
            end as activo,
            precio_costo as precio_costo ,  
            precio_venta as precio_venta
            FROM " . $tableStock . "
            WHERE id_local = " . Auth::user()->id_local . "
            ORDER BY activo desc ,(cantidad - cantidad_minima) ASC
            LIMIT 20;");
        }
        return $listadoProductosStock;
    }

    public function fncTraerStockPack($texto)
    {
        $TablaStockPack = (new ParametriaController)->fncTraeTablaStockPack();
        if (strlen($texto) == 0) {
            $listadoPack = DB::select("
            SELECT  id_pack,descripcion , codigo_pack, codigo, descripcion, precio_venta,activo,cantidad,cantidad_minima
            from " . $TablaStockPack . " 
            WHERE   id_local = " . Auth::user()->id_local . "
            AND     codigo=0
            ;");
        } else {
            $listadoPack = DB::select("
            SELECT  id_pack,descripcion , codigo_pack, codigo, descripcion, precio_venta,activo,cantidad,cantidad_minima
            from " . $TablaStockPack . " 
            WHERE   id_local = " . Auth::user()->id_local . "
            AND     (codigo_pack = '" . $texto . "' OR (descripcion LIKE '%" . $texto . "%' AND codigo =0 ))
            ;");
        }
        return $listadoPack;
    }
    public function index(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $Mensaje = null;
        $Estilo = null;
        $codigo = trim($request->get('codigo'));
        $listadoPack = $this->fncTraerStockPack($codigo);
        return view('StockPack.index', compact('listadoPack', 'Mensaje', 'Estilo', 'codigo'));
    }
    public function create(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $Mensaje = null;
        $Estilo = null;
        $codigo = trim($request->get('codigo'));
        return view('StockPack.create', compact('Mensaje', 'Estilo', 'codigo'));
    }

    public function edit($codigo)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $Mensaje = null;
        $Estilo = null;
        //traer el codigo del pack
        $infoPack = $this->fncTraerStockPack($codigo);
        //dd($infoPack);
        return view('StockPack.edit', compact('Mensaje', 'Estilo', 'codigo', 'infoPack'));
    }

    public function show(request $request, $id)
    {

        $TablaStockPack = (new ParametriaController)->fncTraeTablaStockPack();
        $codigo = $request->query("codigo_pack");

        $up1 = DB::table($TablaStockPack)
            ->where('codigo_pack', $request->query("codigo_pack"))
            ->where('id_local', Auth::user()->id_local)
            ->update([
                'descripcion'     => $request->query("descripcion_pack"),
                'precio_venta'    => $request->query("valor_pack"),
                'cantidad'        => 1,
            ]);

        $Estilo = 'alert alert-success';
        $Mensaje = 'Pack de Productos Actualizado!!!';
        $listadoPack = $this->fncTraerStockPack($codigo);
        return view('StockPack.index', compact('Mensaje', 'Estilo', 'codigo', 'listadoPack'));
    }


    public function update(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login

        $codigo = $request->codigoPack;

        $productos = $this->fncTraerProductosActivos($codigo);

        if (count($productos) > 0) {
            $TablaStockPack = (new ParametriaController)->fncTraeTablaStockPack();
            DB::table($TablaStockPack)
                ->insert(
                    [
                        'id_local'        => Auth::user()->id_local,
                        'activo'          => 1,
                        'codigo_pack'     => $request->codigo_pack,
                        'codigo'          => $productos[0]->codigo,
                        'descripcion'     => $productos[0]->descripcion,
                        'precio_venta'    => 0,
                        'cantidad'        => 1
                    ]
                );
        }
        $Mensaje = "Producto agregado al Pack";
        $Estilo = 'alert alert-success';
        //traer el codigo del pack
        $infoPack = $this->fncTraerStockPack($request->codigo_pack);
        return view('StockPack.edit', ['id' => $codigo], compact('Mensaje', 'Estilo', 'codigo', 'infoPack'));
    }

    public function store(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        $cantidad = $this->fncTraerStockPack($request->codigoPack);

        if (count($cantidad) == 0) {
            $TablaStockPack = (new ParametriaController)->fncTraeTablaStockPack();
            DB::table($TablaStockPack)
                ->insert(
                    [
                        'id_local'        => Auth::user()->id_local,
                        'activo'          => 1,
                        'codigo_pack'     => $request->codigoPack,
                        'codigo'          => 0,
                        'descripcion'     => $request->descripcion,
                        'precio_venta'    => str_replace(".", "", $request->precio_venta),
                        'cantidad'        => 0
                    ]
                );
        }


        $Mensaje = null;
        $Estilo = null;
        $codigo = $request->codigoPack;
        $listadoPack = $this->fncTraerStockPack($codigo);
        return view('StockPack.index', compact('listadoPack', 'Mensaje', 'Estilo', 'codigo'));
    }
}
