<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;

class DevolucionVentaController extends Controller
{

    public function fncTraertotalDevolucion()
    {
        $tablaDevolucionTmp = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();
        $totalDevolucion =
        DB::select("SELECT FORMAT(sum(sub_total),0,'es_CL') as total_dev FROM  ".$tablaDevolucionTmp."
        WHERE id_local = ".Auth::user()->id_local."
        AND id_usuario = ".Auth::user()->id_usuario."
        ")[0]->total_dev;
        return $totalDevolucion;
    }

    public function fncInsertaTablaDevolucion($codigoProducto)
    {
        $tablaDevolucionTmp = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();
    }



    public function fncBuscarProductoDevolucionTMP($codigo)
    {
        $tablaDevolucionTmp = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();
        $listaProducoDevolucion = DB::select("SELECT * FROM  ".$tablaDevolucionTmp." WHERE codigo  = '".$codigo."'");
        return $listaProducoDevolucion;
    }

    public function fncListadoProductoDevolucion()
    {
        $tablaDevolucionTmp = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();
        $listaProducoDevolucion = DB::select("SELECT
            codigo,
            descripcion,
            fecha_venta,
            precio_venta,
            FLOOR(cantidad) as cantidad,
            forma_pago,
            FORMAT(sub_total,0,'es_CL') as sub_total,
            empresa,
            stock
         FROM  ".$tablaDevolucionTmp."
                WHERE  id_local = ".Auth::user()->id_local." AND id_usuario =".Auth::user()->id_usuario."");
        return $listaProducoDevolucion;
    }


    public function index()
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {return view('auth.login');} #Valida Login
        $tablaDevolucionTmp = (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();
        DB::select("DELETE FROM ".$tablaDevolucionTmp."
        WHERE  id_local = ".Auth::user()->id_local." AND id_usuario =".Auth::user()->id_usuario);

        (new VentaController)->fncVentasRespaldasVentasNoRealizadas();

        $listadoProductos = null;
        $ventaTmp=null;
        $totalTmp=null;

        return view('Devolucion.index', compact('listadoProductos','ventaTmp','totalTmp'));
    }
    public function store(Request $request)
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {return view('auth.login');} #Valida Login

        $xaccion=$request->accion;
        $totalTmp=0;
        $tableTmpDetalle= (new ParametriaController)->fncTraeTablaDevolucionDetalleTmp();

        $Mensaje='';
        $Estilo='';

        if(substr($request->codigo,0,5)=="21000")
        {
            $codigo =substr($request->codigo,0,7);
            $peso = substr($request->codigo,7,6)/10000;
            $cantidad = substr($request->codigo,7,6)/10000;
            $listadoProductos = (new VentaController)->fncTraerProductosCodigo($codigo);

            if(count($listadoProductos)>0)
            {
                $descripcion = $listadoProductos[0]->descripcion."(".$listadoProductos[0]->precio_venta.")(".$peso.")";
                $precio_venta = ($listadoProductos[0]->precio_venta);
                $empresa = $listadoProductos[0]->empresa;
                $cantidad_stock  = $listadoProductos[0]->cantidad_stock;
            }
        }
        else
        {
            $codigo = $request->codigo;
            $listadoProductos = (new VentaController)->fncTraerProductosCodigo($codigo);
            $cantidad=1;
            if(count($listadoProductos)==1)
            {
                $descripcion = $listadoProductos[0]->descripcion;
                $precio_venta = $listadoProductos[0]->precio_venta;
                $empresa = $listadoProductos[0]->empresa;
                $cantidad_stock  = $listadoProductos[0]->cantidad_stock;
            }
        }

        $cantidadProdVentaTMP = (new VentaController)->fncBuscarProductoVentasTMP($codigo);
        if($xaccion=="" and $cantidadProdVentaTMP==0)
        {
            if(count($listadoProductos)>0)
            {
                DB::table($tableTmpDetalle)->insert([
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
                    'sub_total'     => $precio_venta*$cantidad
                    ]
                ]);

            }
            else
            {
                $Mensaje='Producto no Encontrado!!!';
                $Estilo='alert alert-danger';
            }
        }
        // VERIFICO LA CANTIDAD PARA INSERTAR O ACTUALIZAR
        $listaProducoDevolucion= $this->fncBuscarProductoDevolucionTMP($codigo);
        if(count($listaProducoDevolucion)>0)
        {
           $cantidadProducto  = $listaProducoDevolucion[0]->cantidad;
        }
        else
        {
            $cantidadProducto  = 0;
        }

        if($cantidadProducto >= 1)
        {
            if($xaccion=="addProducto")
            {
                $cantNew  = intval($cantidadProducto)+1;
                DB::select("UPDATE ".$tableTmpDetalle." SET cantidad = ".$cantNew." ,
                precio_venta = ".$precio_venta.",
                sub_total =  (".$cantNew." * ".$precio_venta.")
                WHERE id_usuario = ".Auth::user()->id_usuario."
                AND  id_local = ".Auth::user()->id_local."
                AND trim(codigo) = '".trim($request->codigo)."';");

                $Mensaje='Producto Agregado !!!';
                $Estilo='alert alert-success';
            }

            if($xaccion == "delProducto")
            {
                $cantNew  = intval($cantidadProducto)-1;
                DB::select("UPDATE ".$tableTmpDetalle." SET cantidad = ".$cantNew." ,
                precio_venta = ".$precio_venta.",
                sub_total =  (".$cantNew." * ".$precio_venta.")
                WHERE id_usuario = ".Auth::user()->id_usuario."
                AND  id_local = ".Auth::user()->id_local."
                AND trim(codigo) = '".trim($request->codigo)."';");

                $Mensaje='Producto Eliminado !!!';
                $Estilo='alert alert-warning';

            }
            $listadoProductos=null;
        }

        $ventaTmp = DB::select("SELECT
        codigo,descripcion,
        CASE
            WHEN substring(CONVERT(cantidad , CHAR), INSTR(CONVERT(cantidad , CHAR) , '.')+1 ,4) = '0000' THEN substring(CONVERT(cantidad , CHAR), 1, INSTR(CONVERT(cantidad , CHAR) , '.')-1)
            ELSE CONVERT(cantidad , CHAR)
        END AS cantidad,
        FORMAT(precio_venta,0,'es_CL') AS precio_venta_ori ,
        FORMAT((cantidad*precio_venta),0,'es_CL') AS precio_venta,
        precio_venta AS precio_venta_ori1,
        sum(stock) as stock
        FROM ".$tableTmpDetalle."
        WHERE id_usuario = ".Auth::user()->id_usuario."
        AND  id_local = ".Auth::user()->id_local."
        GROUP BY codigo,descripcion,precio_venta,cantidad
        ;");
        foreach($ventaTmp as $devTmp)
        {
            $totalTmp += ($devTmp->cantidad*$devTmp->precio_venta_ori1);
        }
        $totalTmp = (new VentaController)->fncFormatoMoneda($totalTmp);
        return view('Devolucion.index', compact('listadoProductos','ventaTmp','totalTmp','Mensaje','Estilo'));

    }


}
