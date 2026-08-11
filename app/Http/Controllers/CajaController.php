<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Ventas;
use App\Models\Configuracion;

class CajaController extends Controller
{
    public function index()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }

        if (Auth::user()->nivel < 10) {
            return redirect()->route('home')->with('Mensaje', 'No tiene permisos para acceder a esta sección')->with('Estilo', 'alert alert-danger');
        }

        $tableVentaDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        // Listamos las ventas agrupadas por folio_caja que estén en estado 1 (pendientes en caja)
        $ventasPendientes = DB::select("
            SELECT 
                folio_caja, 
                id_usuario,
                SUM(sub_total) as total,
                COUNT(*) as items,
                MAX(fecha_venta) as fecha,
                MAX(REPLACE(tipo_pago, 'codBarra', '')) as metodo_pago
            FROM $tableVentaDetalleTmp
            WHERE id_local = " . Auth::user()->id_local . "
            AND estado_caja = 1
            GROUP BY folio_caja, id_usuario
            ORDER BY fecha DESC
        ");

        // Obtenemos nombres de usuarios vendedodres para mostrar
        $usuarios = DB::table('tbl_local_marco_usuarios')->get()->keyBy('id_usuario');

        return view('Caja.index', compact('ventasPendientes', 'usuarios'));
    }

    public function show($folio)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        }

        $tableVentaDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        // Al ver una venta, la marcamos como "En proceso" (estado 2) para el cajero actual
        // Pero el usuario sigue siendo el del vendedor original para mantener la trazabilidad solicitada

        $detalle = DB::table($tableVentaDetalleTmp)
            ->where('folio_caja', $folio)
            ->where('id_local', Auth::user()->id_local)
            ->get();

        if ($detalle->isEmpty()) {
            return redirect()->route('Caja.index')->with('Mensaje', 'Venta no encontrada')->with('Estilo', 'alert alert-danger');
        }

        // Para procesar la venta, la "cargamos" en la vista de ventas pero con el contexto de este folio
        // Redirigimos a una ruta especial o pasamos los datos a la vista de ventas

        // El requerimiento dice: "los usuarios del nivel >= 10 debe ver el total de la venta y poder ver el detalle de esta, ademas de poder ingresar productos nuevos"
        // Vamos a usar la misma vista de Ventas pero filtrando por folio_caja

        return redirect()->route('venta.index', ['folio' => $folio]);
    }

    public function park(Request $request)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return response()->json(['error' => 'No session'], 401);
        }

        $tableVentaDetalleTmp = (new ParametriaController)->fncTraeTablaVentasDetalleTmp();

        // Generar un folio único
        $folio = 'F' . Auth::user()->id_usuario . '-' . time();

        DB::table($tableVentaDetalleTmp)
            ->where('id_usuario', Auth::user()->id_usuario)
            ->where('id_local', Auth::user()->id_local)
            ->where('estado_caja', 0)
            ->update([
                'estado_caja' => 1,
                'folio_caja' => $folio
            ]);

        return redirect()->route('venta.index')->with('Mensaje', 'Venta enviada a Caja correctamente.')->with('Estilo', 'alert alert-success');
    }
}
