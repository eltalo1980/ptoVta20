<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;

class StockPendienteController extends Controller
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
        $table=(new ParametriaController)->fncTraeTablaConfiguracion();
        $infParametros = DB::table($table)
            ->where('idLocal','=',Auth::user()->id_local)
            ->where('categoria',$texto)
            ->get();
        return $infParametros[0]->valor;
    }

    public function fncTraeStockPendientes()
    {
        
        $tablePendiente=(new ParametriaController)->fncTraeTablaStockPendiente();
        $listPendiente = DB::table($tablePendiente)
            ->select('*')
            ->where('id_local','=',Auth::user()->id_local)
            ->distinct()
            ->get();
        return $listPendiente;
    }


    public function fncTraeEmpresasPendientes()
    {
        
        $tablePendiente=(new ParametriaController)->fncTraeTablaStockPendiente();
        $listPendiente = DB::table($tablePendiente)
            ->select('empresa')
            ->distinct()
            ->where('id_local','=',Auth::user()->id_local)
            ->distinct()
            ->get();
        return $listPendiente;
    }



    ///////////////////////////////////
    public function index(Request $request)
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }
        
        $Mensaje = null;
        $Estilo = null;
        $codigo = trim($request->get('codigo'));
        $empresaSeleccionada = trim($request->get('cmbEmpresa'));
        
        // Obtener todas las empresas para el filtro
        $empresasPendientes = $this->fncTraeEmpresasPendientes();
        
        // Aplicar filtros a la lista de productos pendientes
        $listPendiente = $this->fncTraeStockPendientesFiltrados($codigo, $empresaSeleccionada);
        
        if (count($listPendiente) == 0 && (strlen($codigo) > 0 || strlen($empresaSeleccionada) > 0)) {
            $Mensaje = 'No se encontraron productos con los filtros aplicados';
            $Estilo = 'alert alert-warning';
        }
        
        return view('StockPendiente.index', compact('listPendiente', 'empresasPendientes', 'Mensaje', 'Estilo', 'codigo', 'empresaSeleccionada'));
    }

    /**
     * Obtener productos pendientes con filtros aplicados
     */
    public function fncTraeStockPendientesFiltrados($codigo = '', $empresa = '')
    {
        $tablePendiente = (new ParametriaController)->fncTraeTablaStockPendiente();
        $query = DB::table($tablePendiente)
            ->where('id_local', '=', Auth::user()->id_local);
        
        if (strlen($codigo) > 0) {
            $query->where(function($q) use ($codigo) {
                $q->where('codigo', 'LIKE', "%{$codigo}%")
                  ->orWhere('descripcion', 'LIKE', "%{$codigo}%");
            });
        }
        
        if (strlen($empresa) > 0) {
            $query->where('empresa', '=', $empresa);
        }
        
        return $query->orderBy('empresa', 'asc')
                    ->orderBy('descripcion', 'asc')
                    ->get();
    }

    public function show(Request $request,$id)
    {
        
        $parametros = explode("|", str_replace("%7C","|",$request->path()));
        
        $accion=$parametros[1];
        $idProducto=$parametros[2];
        $cantidad=$parametros[3];
        $codigo = str_replace('%20', ' ', $parametros[4]);

        if ($accion=='addCantidad')
        {
            $tablePendiente=(new ParametriaController)->fncTraeTablaStockPendiente();
            $tableStock=(new ParametriaController)->fncTraeTablaStock();
            $listadoProducto = (new StockController)->fncTraerProductosId($idProducto); // obtengo la informacion del producto
            //dd($listadoProducto);
            // Verificar si el producto ya está registrado en la tabla de pendientes
            $existe = DB::table($tablePendiente)
                ->where('id_producto', '=', $idProducto)
                ->where('id_local', '=', Auth::user()->id_local)
                ->exists();

            if ($existe) {
                $Mensaje = 'El producto ya está registrado en el listado de pendientes.';
                $Estilo = 'alert alert-warning';

                $listadoProductosStock = (new StockController)->fncTraerProductos($codigo);
                $listaEmpresas= (new StockController)->fncTraeEmpresas();

                return view('Stock.index', compact('listadoProductosStock', 'listaEmpresas', 'codigo', 'Mensaje', 'Estilo'));
            }

            
            DB::table($tablePendiente)
            ->insert(
                [
                    'id_producto'   => $idProducto,
                    'id_local'      => Auth::user()->id_local,
                    'codigo'        => $listadoProducto[0]->codigo,
                    'descripcion'   => $listadoProducto[0]->descripcion,
                    'empresa'       => $listadoProducto[0]->empresa,
                    'cantidad'      => $cantidad,
                    'precio_costo'  => intval(str_replace('.', '', $listadoProducto[0]->precio_costo)),
                    'precio_venta'  => intval(str_replace('.', '', $listadoProducto[0]->precio_venta))
                    //,'precio_neto'   => intval(str_replace(['.', ','], ['', '.'], $listadoProducto[0]->precio_neto))
                ]
            );
        }

        $listadoProductosStock= (new StockController)->fncTraerProductos($codigo);

        $listaEmpresas= (new StockController)->fncTraeEmpresas();
        $Mensaje='Producto Agregado al Listado de Pendientes!!!';
        $Estilo='alert alert-success';

        return view('Stock.index', compact('listadoProductosStock','listaEmpresas','codigo','Mensaje','Estilo'));
    }


    
    public function destroy(Request $request,$id)
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {return view('auth.login');} #Valida Login

        
        if ($request->id_producto == 'All') {
            return $this->destroyAll( $request->empresa_eliminar);
        }
        //return $request->id_producto;

        $codigo=$request->codigo;
        $tablePendiente=(new ParametriaController)->fncTraeTablaStockPendiente();
        
        DB::table($tablePendiente)
        ->where('id_producto','=',$id)
        ->delete();
        $listPendiente=$this->fncTraeStockPendientes();
        $Mensaje='Producto Eliminado Correctamente!!!';
        $Estilo='alert alert-success';

        return view('StockPendiente.index', compact('listPendiente','Mensaje','Estilo'));

    }
    
    /**
     * Borrar un producto específico de stock pendiente
     */
    public function destroyPendiente($id)
    {
        if(is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }
        
        $tablePendiente = (new ParametriaController)->fncTraeTablaStockPendiente();
        
        DB::table($tablePendiente)
            ->where('id_producto', '=', $id)
            ->where('id_local', '=', Auth::user()->id_local)
            ->delete();
        
        $listPendiente = $this->fncTraeStockPendientes();
        $empresasPendientes=$this->fncTraeEmpresasPendientes();
        $Mensaje = 'Producto eliminado del listado pendiente correctamente!';
        $Estilo = 'alert-success';

        return view('StockPendiente.index', compact('listPendiente', 'empresasPendientes', 'Mensaje', 'Estilo'));
    }

    /**
     * Borrar todos los productos de stock pendiente del local actual
     */
    public function destroyAll($empresa)
    {
        $tablePendiente = (new ParametriaController)->fncTraeTablaStockPendiente();
        $tableLog = (new ParametriaController)->fncTraeTablaLog();
        
        if ($empresa == '' || is_null($empresa))
        {
            // Contar productos antes de eliminar
            $cantidadEliminados = DB::table($tablePendiente)
                ->where('id_local', '=', Auth::user()->id_local)
                ->count();
            
            // Eliminar todos los productos pendientes del local
            DB::table($tablePendiente)
                ->where('id_local', '=', Auth::user()->id_local)
                ->delete();
            
            // Registrar en log
            DB::table($tableLog)->insert([
                'id_local' => Auth::user()->id_local,
                'id_usuario' => Auth::user()->id_usuario,
                'fecha' => now(),
                'descripcion' => 'Eliminación masiva Stock Pendiente - Eliminados: ' . $cantidadEliminados . ' productos - Usr:' . Auth::user()->id_usuario . ' Local:' . Auth::user()->id_local
            ]);
            $listPendiente=null; // No hay productos pendientes después de eliminar todos;
            $empresasPendientes=null;
        }
        else
        {
            // Contar productos antes de eliminar
            $cantidadEliminados = DB::table($tablePendiente)
                ->where('id_local', '=', Auth::user()->id_local)
                ->where('empresa', '=', $empresa)
                ->count();
            
            // Eliminar productos pendientes de la empresa seleccionada
            DB::table($tablePendiente)
                ->where('id_local', '=', Auth::user()->id_local)
                ->where('empresa', '=', $empresa)
                ->delete();
            
            // Registrar en log
            DB::table($tableLog)->insert([
                'id_local' => Auth::user()->id_local,
                'id_usuario' => Auth::user()->id_usuario,
                'fecha' => now(),
                'descripcion' => 'Eliminación masiva Stock Pendiente Empresa: ' . $empresa . ' - Eliminados: ' . $cantidadEliminados . ' productos - Usr:' . Auth::user()->id_usuario . ' Local:' . Auth::user()->id_local
            ]);
            $listPendiente = $this->fncTraeStockPendientes(); // Actualizar lista después de eliminar
            $empresasPendientes = $this->fncTraeEmpresasPendientes();
        }


        $Mensaje = "Se eliminaron {$cantidadEliminados} productos del listado pendiente correctamente!";
        $Estilo = 'alert alert-success';

        return view('StockPendiente.index', compact('listPendiente', 'empresasPendientes', 'Mensaje', 'Estilo'));
    }

    public function fncEliminarTodosPendientes()
    {
        dd("fncEliminarTodosPendientes");

        if(is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }
        
        $tablePendiente = (new ParametriaController)->fncTraeTablaStockPendiente();
        $tableStock = (new ParametriaController)->fncTraeTablaStock();

        // Eliminar todos los pendientes
        DB::table($tablePendiente)
            ->where('id_local', '=', Auth::user()->id_local)
            ->delete();
        
        $listPendiente = $this->fncTraeStockPendientes();
        $Mensaje = "Se Eliminaron Todos los productos pendientes!";
        $Estilo = 'alert-success';
        
        return view('StockPendiente.index', compact('listPendiente', 'Mensaje', 'Estilo'));
    }
}
