@extends('layouts.app')

@section('content')
<div class="container" style="margin-top: 80px;">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="card-premium" style="text-align: center;">
                <div style="margin-bottom: 30px;">
                    <i class="fa fa-shopping-basket fa-4x" style="color: var(--primary); margin-bottom: 20px;"></i>
                    <h2 style="font-weight: 900; color: var(--dark); margin: 0;">DondeElMarco</h2>
                    <p class="text-muted">Sistema de Gestión POS</p>
                </div>

                <form class="form-horizontal" method="POST" action="{{ route('login') }}" style="padding: 0 10px;">
                    @csrf

                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}" style="margin-bottom: 25px; text-align: left;">
                        <label for="email" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">E-Mail</label>
                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus style="height: 45px; border-radius: 8px;">
                        @if ($errors->has('email'))
                        <span class="help-block small"><strong>{{ $errors->first('email') }}</strong></span>
                        @endif
                    </div>

                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}" style="margin-bottom: 25px; text-align: left;">
                        <label for="password" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;">Contraseña</label>
                        <input id="password" type="password" class="form-control" name="password" required style="height: 45px; border-radius: 8px;">
                        @if ($errors->has('password'))
                        <span class="help-block small"><strong>{{ $errors->first('password') }}</strong></span>
                        @endif
                    </div>

                    <div class="checkbox" style="text-align: left; margin-bottom: 25px;">
                        <label style="font-size: 13px; color: #64748b;">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Mantener sesión iniciada
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-rounded btn-lg btn-block">
                        Iniciar Sesión
                    </button>

                    <div style="margin-top: 20px;">
                        <a class="btn btn-link" href="{{ route('password.request') }}" style="font-size: 13px; color: #64748b;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection