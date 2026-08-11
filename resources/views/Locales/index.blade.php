@extends('layouts.app')
@section('content')


<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <strong>Adm Locales</strong>
            <div class="row">
            <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                
                            </span>
                            @if (Auth::user()->nivel >= 10)
                             <div class="float-right">
                               <!--  <a href="{{ route('venta.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left"> -->
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
                                
                <form class="form-horizontal" name="form1" id="form1" method="GET" action="{{route('stock.index')}}">
                    @csrf
                    <div class="row">
				        <div class="col-md-6">
                            <a href="{{ route('Locales.create') }}" class="btn btn-success btn-sm">Crear</a>
                        </div>
                    </div>

                    
                    <!-- productos seleccionados-->
                    <!-- Encuestas -->
                    @if(isset($listadolocales) and count($listadolocales)>0 )
                        <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">Id</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">fecha_expiracion</th>
                                <th scope="col">valor_plan</th>

                            </tr>
                        </thead>
                            <tbody>
                                @foreach($listadolocales as $lslcs)  
                                    <tr class="{{$lslcs->activo==0 ? 'danger':'' }}">
                                        <td>
                                            <form action="{{ route('Locales.edit',$lslcs->id_local) }}" method="POST">   
                                            <a href="{{ route('Locales.edit',$lslcs->id_local) }}" class="btn btn-primary btn-xs pull-right"><i class="glyphicon glyphicon-pencil"></i></a>
                                            </form>
                                        </td>                                        
                                        <td>{{$lslcs->id_local}}</td>
                                        <td>{{$lslcs->nombre_local}}</td>
                                        <td>{{$lslcs->fecha_expiracion}}</td>
                                        <td>{{$lslcs->valor_plan}}</td>                                        
                                        
                                    </tr>
                                @endforeach 
                            </tbody>
                        </table>
                    @endif            
   
            </form>

            <!--Section: Shadows-->
        </section>
      </section>
      <!-- Section: Components -->
    </div>
@endsection
