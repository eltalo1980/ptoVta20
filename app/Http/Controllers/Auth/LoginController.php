<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        $tablePagos = (new \App\Http\Controllers\ParametriaController)->fncTraeTablaPagos();
        $pago = \Illuminate\Support\Facades\DB::select("SELECT * FROM $tablePagos where id_local = " . $user->id_local . " and now() between fecha_inicio and fecha_fin");

        if (count($pago) == 0) {
            $mensaje = 'No se Registran Pagos de la aplicacion para este Mes';
            return redirect()->route('venta.index')->with(['Mensaje' => $mensaje, 'Estilo' => 'alert alert-danger']);
        }
    }
}
