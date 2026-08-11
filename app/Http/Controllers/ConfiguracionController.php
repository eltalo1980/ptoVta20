<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the configurations.
     */
    public function index()
    {
        if (Auth::user()->nivel < 10) {
            return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $infConfiguracion = Configuracion::where('nivel', '<=', Auth::user()->nivel)
            ->where(function ($query) {
                if (Auth::user()->nivel < 100) {
                    $query->where('idLocal', Auth::user()->id_local);
                }
            })->get();

        return view('Configuracion.index', compact('infConfiguracion'));
    }

    /**
     * Show the form for creating a new configuration.
     */
    public function create()
    {
        if (Auth::user()->nivel < 100) {
            return redirect()->route('Configuracion.index')->with('error', 'No tiene permisos para crear variables.');
        }

        return view('Configuracion.create');
    }

    /**
     * Store a newly created configuration or update existing ones.
     */
    public function store(Request $request)
    {
        if (Auth::user()->nivel < 10) {
            return redirect()->route('home')->with('error', 'No tiene permisos.');
        }

        if ($request->accion == "crearVariable") {
            $request->validate([
                'variable' => 'required|string|max:255',
                'valor' => 'required',
                'id_local' => 'required|integer',
                'cmbTipoPregunta' => 'required|string',
                'nivel' => Auth::user()->nivel >= 100 ? 'required|integer' : 'nullable'
            ]);

            $nivelVariable = Auth::user()->nivel >= 100 ? $request->nivel : 10;

            Configuracion::create([
                'idLocal' => $request->id_local,
                'categoria' => $request->variable,
                'valor' => $request->valor,
                'descripcion' => $request->descripcion,
                'tipoValores' => $request->cmbTipoPregunta,
                'nivel' => $nivelVariable
            ]);

            return redirect()->route('Configuracion.index')->with('success', 'Variable creada correctamente.');
        }

        // Batch Update - Filter by level as well
        $configs = Configuracion::where('nivel', '<=', Auth::user()->nivel)
            ->where(function ($query) {
                if (Auth::user()->nivel < 100) {
                    $query->where('idLocal', Auth::user()->id_local);
                }
            })->get();

        foreach ($configs as $config) {
            if ($request->has($config->idConfiguracion)) {
                $config->update([
                    'valor' => $request->input($config->idConfiguracion)
                ]);
            }
        }

        return redirect()->route('Configuracion.index')->with('success', 'Configuración actualizada correctamente.');
    }
}
