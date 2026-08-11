@extends('layouts.app')
@section('content')
<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <h3 class="text-center"><strong>Clases</strong></h3>
            <div class="row">
            <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                
                            </span>
                            @if (Auth::user()->nivel>=10)
                             <div class="float-right">
                                <a href="{{ route('clases.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Nueva Clase') }}
                                </a>
                              </div>
                            @endif  
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

              <div class="col-md-6 mb-4">
                <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Encuestas -->
                    @if(isset($encNivelRealizar) and count($encNivelRealizar)>0 )

                        <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($encNivelRealizar as $encUsr)  
                                <tr>
                                <td>{{$encUsr->nombreEvaluacion}}</td>
                                <td>{{$encUsr->fechaEvaluacion}}</td>                  
                                <td>
                                    <form action="{{ route('EvaluacionRespuesta.destroy',$encUsr->idEvaluacion) }}" method="POST">
                                        <a class="btn btn-info btn-sm" href="{{ route('EvaluacionRespuesta.show',$encUsr->idEvaluacion) }}">Responder</a>
                                    </form>
                                </td>                        
                                </tr>
                            @endforeach 
                        </tbody>
                        </table>
                    @endif            
                    <!-- Encuestas -->
                    <!-- Clases -->
                    {{ __('Clases') }}
                    @if (Auth::user()->nivel>=10)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clases as $clase)
                                    <tr>
                                        <td>{{ $clase->fecha}}</td>
                                        <td>
                                            <form action="{{ route('clases.destroy',$clase->id_clase) }}" method="POST">
                                                @if(auth::user()->nivel < 10)
                                                    <a class="btn btn-sm btn-success" href="{{ route('asistencia.create','parametros=fecha|'.$clase->fecha.'|OPT|1') }}"><i class="fa fa-fw fa-edit"></i>Asistire Mi Bloque</a>
                                                    <!-- <a class="btn btn-sm btn-warning" href="{{ route('asistencia.create','parametros=fecha|'.$clase->fecha.'|OPT|2') }}"><i class="fa fa-fw fa-edit"></i>Asistire Otro Bloque</a> -->
                                                    <a class="btn btn-sm btn-danger" href="{{ route('asistencia.create','parametros=fecha|'.$clase->fecha.'|OPT|3') }}"><i class="fa fa-fw fa-edit"></i>No Asistire</a>
                                                @else
                                                    <a class="btn btn-xs btn-primary " href="{{ route('clases.show',$clase->fecha) }}"><i class="fa fa-fw fa-eye"></i> Ver</a>
                                                    <a class="btn btn-xs btn-danger" href="{{ route('asistencia.destroy',$clase->id_clase) }}"><i class="fa fa-fw fa-edit"></i>Eliminar</a>
                                                @endif

                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tbody>
                                @foreach ($Asistencias as $As)
                                    <tr>
                                        <td class=@if($As->opcion==1) {{'bg-success'}} @else {{'bg-danger'}} @endif>{{$As->fecha_asistencia}}</td>
                                        <td class=@if($As->opcion==1) {{'bg-success'}} @else {{'bg-danger'}} @endif>@if($As->opcion==1) {{'Presente'}} @else {{'Ausente'}}@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else 
                        @if($AsistenciasHoyCantidad != 1)
                            <tbody>
                                @foreach ($clases as $clase)
                                    <tr>
                                        <td>{{ $clase->fecha}}</td>
                                        <td>
                                            <form action="{{ route('clases.destroy',$clase->id_clase) }}" method="POST">
                                                @if(auth::user()->nivel < 10)
                                                    <a class="btn btn-sm btn-success" href="{{ route('asistencia.create','parametros=fecha|'.$clase->fecha.'|OPT|1') }}"><i class="fa fa-fw fa-edit"></i>Asistire Mi Bloque</a>
                                                    <!--<a class="btn btn-sm btn-warning" href="{{ route('asistencia.create','parametros=fecha|'.$clase->fecha.'|OPT|2') }}"><i class="fa fa-fw fa-edit"></i>Asistire Otro Bloque</a>-->
                                                    <a class="btn btn-sm btn-danger" href="{{ route('asistencia.create','parametros=fecha|'.$clase->fecha.'|OPT|3') }}"><i class="fa fa-fw fa-edit"></i>No Asistire</a>
                                                @else
                                                    <a class="btn btn-sm btn-primary " href="{{ route('clases.show',$clase->fecha) }}"><i class="fa fa-fw fa-eye"></i> Ver</a>
                                                    <a class="btn btn-sm btn-danger" href="{{ route('asistencia.destroy',$clase->id_clase) }}"><i class="fa fa-fw fa-edit"></i>Eliminar</a>
                                                @endif

                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @else
                            <tbody>
                                @foreach ($Asistencias as $As)
                                    <tr>
                                        <td class=@if($As->opcion==1) {{'bg-success'}} @else {{'bg-danger'}} @endif>{{$As->fecha_asistencia}}</td>
                                        <td class=@if($As->opcion==1) {{'bg-success'}} @else {{'bg-danger'}} @endif>@if($As->opcion==1) {{'Presente'}} @else {{'Ausente'}}@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    @endif                                    
                </table>
            </form>

            <!--Section: Shadows-->
        </section>
      </section>
      <!-- Section: Components -->
    </div>
@endsection

