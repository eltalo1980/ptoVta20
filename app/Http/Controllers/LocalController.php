<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // para ver la informacion del usuario
use App\User;
use Carbon\Carbon;



class LocalController extends Controller
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

    public function fncListaLocales()
    {
        $tablaLocales = (new ParametriaController)->fncTraeTablaLocales();
        $listadolocales = DB::select("SELECT 
        id_local,nombre_local,activo,fecha_expiracion,valor_plan 
        FROM " . $tablaLocales . ";");
        return $listadolocales;
    }
    public function index()
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        if (Auth::user()->nivel < 100) {
            return view('auth.login');
        } #Valida Nivel

        $listadolocales = $this->fncListaLocales();

        $Estilo = null;
        $Mensaje = null;
        return view('Locales.index', compact('listadolocales', 'Estilo', 'Mensaje'));
    }
    public function edit($idLocal)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        if (Auth::user()->nivel < 100) {
            return view('auth.login');
        } #Valida Nivel

        $tablaLocales = (new ParametriaController)->fncTraeTablaLocales();
        $localEditar = DB::select("SELECT 
        id_local,nombre_local,activo,fecha_expiracion,valor_plan 
        FROM " . $tablaLocales . "
        WHERE id_local = " . $idLocal . "
        ");
        $Estilo = null;
        $Mensaje = null;

        return view('Locales.edit', compact('localEditar', 'Estilo', 'Mensaje'));
    }

    public function update(Request $request, $idLocal)
    {
        if (is_null(Auth::user()) || empty(Auth::user())) {
            return view('auth.login');
        } #Valida Login
        if (Auth::user()->nivel < 100) {
            return view('auth.login');
        } #Valida Nivel

        $date = Carbon::parse($request->input('fecha_expiracion'));
        $dateOK = $date->format('Y-m-d');

        $tablaLocales = (new ParametriaController)->fncTraeTablaLocales();
        $tablaUsuarios = (new ParametriaController)->fncTraeTablaUsuarios();

        DB::table($tablaLocales)
            ->where('id_local', '=', $idLocal)
            ->update(
                [
                    'activo'            => intval($request->cmbActivo),
                    'fecha_expiracion'  => $dateOK,
                    'valor_plan'        => $request->valor_plan
                ]
            );

        DB::table($tablaUsuarios)
            ->where('id_local', '=', $idLocal)
            ->update(
                [
                    'fecha_expiracion'  => $dateOK
                ]
            );

        $listadolocales = $this->fncListaLocales();
        $Estilo = 'alert alert-success';
        $Mensaje = 'Informacion Actualizada Correctamente !!!';
        return view('Locales.index', compact('listadolocales', 'Estilo', 'Mensaje'));
    }
}
