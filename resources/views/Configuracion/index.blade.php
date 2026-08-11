@extends('layouts.app')

@section('content')
<div class="container" style="margin-top: 30px;">
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="panel panel-default border-none shadow-premium">
        <div class="panel-heading bg-primary-gradient p-25">
          <div class="row">
            <div class="col-xs-8">
              <h3 class="panel-title text-white">
                <i class="fa fa-cogs" aria-hidden="true"></i> Configuración del Sistema
              </h3>
              <p class="text-white-50 small mb-0"><i class="fa fa-asterisk"></i> Las variables con asterisco son de nivel Administrador.</p>
            </div>
            <div class="col-xs-4 text-right">
              @if(Auth::user()->nivel >= 100)
              <a href="{{ route('Configuracion.create') }}" class="btn btn-default btn-sm btn-rounded-custom shadow-sm">
                <i class="fa fa-plus" aria-hidden="true"></i> Nueva Variable
              </a>
              @endif
            </div>
          </div>
        </div>
        <div class="panel-body p-30">
          @if(Session::has('success'))
          <div class="alert alert-success alert-dismissible shadow-sm mb-20" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fa fa-check-circle"></i> {{ Session::get('success') }}
          </div>
          @endif

          @if(Session::has('error'))
          <div class="alert alert-danger alert-dismissible shadow-sm mb-20" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fa fa-exclamation-triangle"></i> {{ Session::get('error') }}
          </div>
          @endif

          <form method="POST" action="{{ route('Configuracion.store') }}" role="form">
            @csrf
            <div class="table-responsive">
              <table class="table table-hover table-custom align-middle">
                <thead>
                  <tr class="bg-light-gray">
                    <th class="p-15 border-none">Variable</th>
                    <th class="p-15 border-none">Descripción</th>
                    <th class="p-15 border-none" style="min-width: 250px;">Valor</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($infConfiguracion as $InfConf)
                  <tr>
                    <td class="p-15 border-top-light text-primary bold">
                      {{ $InfConf->categoria }}@if($InfConf->nivel >= 100)<span class="text-danger">*</span>@endif
                    </td>
                    <td class="p-15 border-top-light text-muted">
                      {{ $InfConf->descripcion }}
                    </td>
                    <td class="p-15 border-top-light">
                      @if($InfConf->tipoValores == 'truefalse')
                      <select name="{{ $InfConf->idConfiguracion }}" class="form-control input-rounded border-light shadow-xs">
                        <option value="1" {{ $InfConf->valor == 1 ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ $InfConf->valor == 0 ? 'selected' : '' }}>Inactivo</option>
                      </select>
                      @elseif($InfConf->tipoValores == 'objectSize')
                      <select name="{{ $InfConf->idConfiguracion }}" class="form-control input-rounded border-light shadow-xs">
                        <option value="chico" {{ $InfConf->valor == 'chico' ? 'selected' : '' }}>Chico</option>
                        <option value="mediano" {{ $InfConf->valor == 'mediano' ? 'selected' : '' }}>Mediano</option>
                        <option value="grande" {{ $InfConf->valor == 'grande' ? 'selected' : '' }}>Grande</option>
                      </select>
                      @elseif($InfConf->tipoValores == 'entero')
                      <input type="number" name="{{ $InfConf->idConfiguracion }}" value="{{ $InfConf->valor }}" class="form-control input-rounded border-light shadow-xs">
                      @else
                      <input type="text" name="{{ $InfConf->idConfiguracion }}" value="{{ $InfConf->valor }}" class="form-control input-rounded border-light shadow-xs">
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <hr class="mt-30">
            <div class="text-right">
              <button type="submit" class="btn btn-primary btn-lg btn-rounded-custom shadow-md px-40">
                <i class="fa fa-floppy-o" aria-hidden="true"></i> Guardar Cambios
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Custom spacing and typography */
  .p-25 {
    padding: 25px !important;
  }

  .p-30 {
    padding: 30px !important;
  }

  .p-15 {
    padding: 15px !important;
  }

  .mb-20 {
    margin-bottom: 20px !important;
  }

  .mt-30 {
    margin-top: 30px !important;
  }

  .bold {
    font-weight: 700 !important;
  }

  .px-40 {
    padding-left: 40px !important;
    padding-right: 40px !important;
  }

  /* Modern Colors and Gradients */
  .bg-primary-gradient {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    border-bottom: none !important;
  }

  .text-white {
    color: #ffffff !important;
  }

  .text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important;
  }

  .text-muted {
    color: #858796 !important;
  }

  .bg-light-gray {
    background-color: #f8f9fc !important;
  }

  /* Shadows */
  .shadow-premium {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
  }

  .shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
  }

  .shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
  }

  .shadow-xs {
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075) !important;
  }

  /* Borders */
  .border-none {
    border: none !important;
  }

  .border-light {
    border: 1px solid #e3e6f0 !important;
  }

  .border-top-light {
    border-top: 1px solid #e3e6f0 !important;
  }

  /* Rounded helpers */
  .btn-rounded-custom {
    border-radius: 50px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease;
  }

  .input-rounded {
    border-radius: 8px !important;
    height: 40px !important;
  }

  /* Table Enhancements */
  .table-custom thead th {
    font-size: 12px;
    text-transform: uppercase;
    color: #4e73df;
    letter-spacing: 1px;
  }

  .panel-title {
    font-size: 20px;
    font-weight: 700;
  }
</style>
@endsection