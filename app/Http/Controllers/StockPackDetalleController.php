<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;



class StockPackDetalleController extends Controller
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
    public function fncInfoPack($id)
    {
        $TablaStockPack= (new ParametriaController)->fncTraeTablaStockPack(); 
        $listadoPack = DB::select("
        SELECT  id_pack,descripcion , codigo_pack, codigo, descripcion, precio_venta,cantidad, activo
        from ".$TablaStockPack." 
        WHERE   id_local = ".Auth::user()->id_local."
        AND     codigo = 0
        AND     codigo_pack = '".$id."' ;");
        return $listadoPack;
    }
    public function fncProductosPack($id)
    {
        $TablaStockPack= (new ParametriaController)->fncTraeTablaStockPack(); 
        $listadoPack = DB::select("
        SELECT  id_pack,descripcion , codigo_pack, codigo, descripcion, precio_venta,cantidad, activo
        from ".$TablaStockPack." 
        WHERE   id_local = ".Auth::user()->id_local."
        AND     codigo != 0
        AND     codigo_pack = '".$id."' ;");
        return $listadoPack;
    }    
    public function fncTraeProducto($codigoProducto)
    {
        $tableStock=(new ParametriaController)->fncTraeTablaStock();
        $listadoProductosStock = DB::select("SELECT 
            id_producto,empresa,codigo,descripcion,cantidad,cantidad_minima,
            case when activo = true then 1
                when activo = false then 0
            end as activo,
            replace(FORMAT(precio_costo, 0),',','.') as precio_costo,  
            replace(FORMAT(precio_venta, 0),',','.') as precio_venta,
            cantidad_venta_mayor,
            replace(FORMAT(precio_venta_mayor, 0),',','.') as precio_venta_mayor
        FROM ".$tableStock."
        WHERE id_local = ".Auth::user()->id_local."
        AND (codigo = '$codigoProducto')
        ORDER BY activo desc ,descripcion ASC;");
        return $listadoProductosStock;
    }    
    public function fncTraeProductoEnPack($codigoPack,$codigoProducto)
    {
        $tableStockPack=(new ParametriaController)->fncTraeTablaStockPack();
        $listadoProductosEnPack = DB::select("SELECT 
            id_pack,codigo_pack,codigo,descripcion,cantidad,
            case when activo = true then 1
                when activo = false then 0
            end as activo,
            replace(FORMAT(precio_costo, 0),',','.') as precio_costo,  
            replace(FORMAT(precio_venta, 0),',','.') as precio_venta
        FROM ".$tableStockPack."
        WHERE id_local = ".Auth::user()->id_local."
        AND (codigo_pack = '$codigoPack')
        AND (codigo = '$codigoProducto')
        ORDER BY activo desc ,descripcion ASC;");
        return $listadoProductosEnPack;
    }    

    public function show(Request $request, $codigo)
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {return view('auth.login');} #Valida Login
        $codigoProducto=$request->query("codigo");
        $codigoPack=$request->query("codigo_pack");
        $listaProducto = $this->fncTraeProducto($codigoProducto);

        if(count($listaProducto) > 0)
        {
            $TablaStockPack= (new ParametriaController)->fncTraeTablaStockPack(); 
            DB::table($TablaStockPack)
            ->insert(
                [
                    'id_local'        => Auth::user()->id_local,
                    'activo'          => 1,
                    'codigo_pack'     => $codigoPack,
                    'codigo'          => $listaProducto[0]->codigo,
                    'descripcion'     => $listaProducto[0]->descripcion,
                    'precio_venta'    => 0,
                    'cantidad'        => 1,
                    'ultima_actualizacion' => now(),
                    'actualizacion_estado' => 'pendiente'
                ]
                );
        }
        $Mensaje="Producto agregado al Pack";
        $Estilo='alert alert-success';
        //traer el codigo del pack
        $infoPack = $this->fncInfoPack($codigo);
        $detallePack = $this->fncProductosPack($codigo);
        return view('StockPackDetalle.edit', compact('Mensaje','Estilo','codigo','infoPack','detallePack'));
    }
    public function index1(Request $request)
    {
        dd($request);
    }
    public function index(Request $request)
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {return view('auth.login');} #Valida Login
        $codigoProducto=$request->query("codigo");
        $codigoPack=$request->query("codigo_pack");
        $codigo=$request->query("codigo_pack");
        $listaProducto = $this->fncTraeProducto($codigoProducto);
        $listaProductoEnPack = $this->fncTraeProductoEnPack($codigoPack,$codigoProducto);
        $TablaStockPack= (new ParametriaController)->fncTraeTablaStockPack(); 
        
        // Agrego cantidad al codigo en el pack
        if(count($listaProductoEnPack) > 0)
        {
            $up1 = DB::table($TablaStockPack)
            ->where('codigo_pack',$codigoPack)
            ->where('codigo',$codigoProducto)
            ->where('id_local',Auth::user()->id_local)
            ->update([
                'cantidad'     => count($listaProductoEnPack) + 1,
                'ultima_actualizacion' => now(),
                'actualizacion_estado' => 'pendiente'
            ]);
        }


        if((count($listaProducto) > 0) and (count($listaProductoEnPack)==0))
        {
            DB::table($TablaStockPack)
            ->insert(
                [
                    'id_local'        => Auth::user()->id_local,
                    'activo'          => 1,
                    'codigo_pack'     => $codigoPack,
                    'codigo'          => $listaProducto[0]->codigo,
                    'descripcion'     => $listaProducto[0]->descripcion,
                    'precio_venta'    => 0,
                    'cantidad'        => 1,
                    'ultima_actualizacion' => now(),
                    'actualizacion_estado' => 'pendiente'
                ]
                );
        }
        $Mensaje="Producto agregado al Pack";
        $Estilo='alert alert-success';
        //traer el codigo del pack
        $infoPack = $this->fncInfoPack($codigo);
        $detallePack = $this->fncProductosPack($codigo);
        return view('StockPackDetalle.edit', compact('Mensaje','Estilo','codigo','infoPack','detallePack'));
    }

    public function destroy(Request $request, $codigo)
    {

        $codigo=$request->codigo_pack;
        $TablaStockPack= (new ParametriaController)->fncTraeTablaStockPack(); 
        DB::select("DELETE FROM $TablaStockPack
        WHERE id_local = ".Auth::user()->id_local."
        AND codigo = '$request->codigo_producto'
        AND codigo_pack = '$request->codigo_pack';");

        $Mensaje="Producto Eliminado del Pack";
        $Estilo='alert alert-success';
        //traer el codigo del pack
        $infoPack = $this->fncInfoPack($codigo);
        $detallePack = $this->fncProductosPack($codigo);
        return view('StockPackDetalle.edit', compact('Mensaje','Estilo','codigo','infoPack','detallePack'));
    }

}
