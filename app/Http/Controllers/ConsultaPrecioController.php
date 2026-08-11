<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ParametriaController;
use App\Http\Controllers\VentaController;

class ConsultaPrecioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }

        $parametria = new ParametriaController();
        $table = $parametria->fncTraeTablaConsultaPrecio();
        $tiempoVerConsultaPrecio = $parametria->fncTraeConfiguracion('tiempoVerConsultaPrecio') ?? 1; // Default 1 second

        $productosConsulta = DB::select("SELECT * FROM $table 
            WHERE id_usuario = " . Auth::user()->id_usuario . " 
            AND id_local = " . Auth::user()->id_local . "
            ORDER BY id_ventas DESC");

        return view('ConsultaPrecio.index', compact('productosConsulta', 'tiempoVerConsultaPrecio'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return redirect()->route('login');
        }

        $codigo = trim($request->codigo);
        $tableConsulta = (new ParametriaController)->fncTraeTablaConsultaPrecio();
        $ventaController = new VentaController();

        // Buscar producto usando la lógica flexible de VentaController (exacta y por LIKE)
        $listadoProductos = $ventaController->fncTraerProductosCodigo($codigo);



        if (count($listadoProductos) > 0) {
            foreach ($listadoProductos as $producto) {
                try {
                    DB::table($tableConsulta)->insert([
                        'id_local' => Auth::user()->id_local,
                        'id_usuario' => Auth::user()->id_usuario,
                        'codigo' => $producto->codigo ?? ($producto->codigo_pack ?? $codigo),
                        'descripcion' => $producto->descripcion,
                        'precio_venta' => $producto->precio_venta,
                        'empresa' => $producto->empresa,
                        'stock' => $producto->cantidad_stock ?? 0,
                        'fecha_venta' => now(),
                        'cantidad' => 1,
                        'sub_total' => $producto->precio_venta,
                        'actualizacion_estado' => 'CONSULTA'
                    ]);
                } catch (\Exception $e) {
                }
            }

            return redirect()->route('ConsultaPrecio.index', ['minimal' => 1])->with('Mensaje', 'Resultados encontrados: ' . count($listadoProductos))->with('Estilo', 'alert alert-success');
        }

        return redirect()->route('ConsultaPrecio.index', ['minimal' => 1])->with('Mensaje', 'Producto no encontrado')->with('Estilo', 'alert alert-danger');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = (new ParametriaController)->fncTraeTablaConsultaPrecio();

        if ($id == 'all') {
            DB::table($table)
                ->where('id_usuario', Auth::user()->id_usuario)
                ->where('id_local', Auth::user()->id_local)
                ->delete();
        } else {
            DB::table($table)
                ->where('id_ventas', $id)
                ->where('id_usuario', Auth::user()->id_usuario)
                ->where('id_local', Auth::user()->id_local)
                ->delete();
        }

        return redirect()->route('ConsultaPrecio.index', ['minimal' => 1]);
    }

    /**
     * Clear all consultation records for the current user via AJAX.
     *
     * @return \Illuminate\Http\Response
     */
    public function clear()
    {
        $table = (new ParametriaController)->fncTraeTablaConsultaPrecio();

        DB::table($table)
            ->where('id_usuario', Auth::user()->id_usuario)
            ->where('id_local', Auth::user()->id_local)
            ->delete();

        return response()->json(['status' => 'success']);
    }
}
