@extends('layouts.app')

@section('content')
    <section class="content container-fluid">
    <div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Edicion de Clase</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
						<form method="POST" action="{{ route('clases.update',$clase->id_clase) }}"  role="form">
                            @csrf
							@method('PUT')
							<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <input type="hidden" name="id_clase"  class="form-control" value ="{{$clase->id_clase}}" >
										<strong>Fecha:</strong>
                                        <input type="text" name="fecha"  class="form-control" value ="{{substr($clase->fecha,8,2).'-'.substr($clase->fecha,5,2).'-'.substr($clase->fecha,0,4)}}" placeholder="Titulo Leccion">
                                        <strong>Titulo:</strong>
                                        <input type="text" name="titulo"  class="form-control" value ="{{$clase->titulo}}" placeholder="Titulo Leccion">
                                        <strong>tiempo:</strong>
                                        <input type="number" name="duracion"  class="form-control" value ="{{$clase->duracion}}" placeholder="10" >
                                        <strong>Descripcion:</strong>
                                        <textarea rows="4", cols="20" class="form-control input-sm" id="descripcion" name="descripcion" style="resize:none, ">{{$clase->descripcion}}</textarea>
										<strong>orden:</strong>
                                        <input type="number" name="orden"  class="form-control" placeholder="10" value="{{$clase->orden}}">
									</div>
                                </div>
                            </div>    

							<div class="row">
 								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Actualizar" class="btn btn-success btn-block">
								</div>	
								<div class="col-xs-3 col-sm-3 col-md-3">
									<a href="{{ route('clases.index') }}" class="btn btn-danger btn-block" >Atrás</a>
								</div>	
								
 							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
    </section>
@endsection
