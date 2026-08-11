<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB; // para ejecutar SP
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;
use App\Tdmparametros;
use Carbon\Carbon;

class ParametriaController extends Controller
{


    public function fncTraeTabla($tabla)
    {
        $table = $this->fncTraeTablaConfiguracion();
        $infParametros = DB::table($table)
            ->where('id_local', '=', Auth::user()->id_local)
            ->where('variable', $tabla)
            ->get();
        return $infParametros[0]->valor;
    }
    public function fncTraeTablaLocales()
    {
        return "tbl_locales";
    }
    public function fncTraeTablaPagos()
    {
        $table = 'tbl_master_pagos';
        return $table;
    }
    public function fncTraeTablaUsuarios()
    {
        $table = 'tbl_local_marco_usuarios';
        return $table;
    }
    public function fncTraeTablaStock()
    {
        $table = 'tbl_local_marco_stock';
        return $table;
    }
    public function fncTraeTablaStockPack()
    {
        $table = 'tbl_local_marco_stock_pack';
        return $table;
    }
    public function fncTraeTablaCaja()
    {
        $table = 'tbl_local_marco_caja';
        return $table;
    }
    public function fncTraeTablaVentasTotales()
    {
        $table = 'tbl_local_marco_ventas_total';
        return $table;
    }
    public function fncTraeTablaVentas()
    {
        $table = 'tbl_local_marco_ventas';
        return $table;
    }
    public function fncTraeTablaFacturas()
    {
        $table = 'tbl_local_marco_facturas';
        return $table;
    }
    public function fncTraeTablaLog()
    {
        $table = 'tbl_local_marco_log';
        return $table;
    }
    public function fncTraeTablaVentasTotalTmp()
    {
        $table = 'tbl_local_marco_total_tmp';
        return $table;
    }
    public function fncTraeTablaConfiguracion()
    {
        $table = 'tbl_local_marco_cofiguracion';
        return $table;
    }
    public function fncTraeTablaVentasDetalleTmp()
    {
        $table = 'tbl_local_marco_ventas_detalle_tmp';
        return $table;
    }
    public function fncTraeTablaStockPendiente()
    {
        $table = 'tbl_local_marco_stock_pendiente';
        return $table;
    }
    public function fncTraeTablaDevolucionDetalleTmp()
    {
        return 'tbl_local_marco_devolucion_detalle_tmp';
    }
    public function fncTraeTablaDevolucionDetalleFinal()
    {
        return 'tbl_local_marco_devolucion_detalle_final';
    }
    public function fncTraeTablaConsultaPrecio()
    {
        return 'tbl_local_marco_consulta_precio';
    }

    public function fncTraeTablaVentasDetalleTmpBorrada()
    {
        $table = 'tbl_local_marco_ventas_detalle_tmp_borrada';
        return $table;
    }

    public function fncTraeTablaMaestraProductos()
    {
        $table = 'tbl_master_productos';
        return $table;
    }
    public function fncTraeProcedimientoGuardaVenta()
    {
        $procedimiento = 'sp_local_marco_guarda_venta';
        return $procedimiento;
    }
    public function fncCajaMontoInicial()
    {
        $tablaConfiguracion = $this->fncTraeTablaConfiguracion();
        $dineroInicialCaja = DB::select("SELECT valor FROM $tablaConfiguracion WHERE idLocal = " . Auth::user()->id_local . "
        AND categoria='dineroInicialCaja'");
        return $dineroInicialCaja[0]->valor;
    }

    public function fncTraeConfiguracion($texto)
    {
        $table = $this->fncTraeTablaConfiguracion();
        $infParametros = DB::table($table)
            ->where('idLocal', '=', Auth::user()->id_local)
            ->where('categoria', $texto)
            ->get();
        if (count($infParametros) == 0) {
            return null;
        } else {
            return $infParametros[0]->valor;
        }
    }

    public function fncButtonSize()
    {
        /*
        $tableConfiguracion =$this->fncTraeTablaConfiguracion();
        $sizeButtonORI = DB::select("
        SELECT valor
        FROM ".$tableConfiguracion."
        WHERE idLocal = ".Auth::user()->id_local."
        AND categoria= 'buttonSize'");
        if($sizeButtonORI[0]->valor=='chico')
        {
            $sizeButton='btn-sm';
        }
        if($sizeButtonORI[0]->valor=='mediano')
        {
            $sizeButton='';
        }
        if($sizeButtonORI[0]->valor=='grande')
        {
            $sizeButton='btn-lg';
        }
        */
        $sizeButton = 'btn-lg';
        return $sizeButton;
    }
    public function fncTextoSize()
    {
        /*
        $tableConfiguracion =$this->fncTraeTablaConfiguracion();
        $sizeTextORI = DB::select("
        SELECT valor
        FROM ".$tableConfiguracion."
        WHERE idLocal = ".Auth::user()->id_local."
        AND categoria= 'textSize'");
        if($sizeTextORI[0]->valor=='chico')
        {
            $sizeText='h6';
        }
        if($sizeTextORI[0]->valor=='mediano')
        {
            $sizeText='h3';
        }
        if($sizeTextORI[0]->valor=='grande')
        {
            $sizeText='h1';
        }
        */
        $sizeText = 'h1';
        return $sizeText;
    }
}
