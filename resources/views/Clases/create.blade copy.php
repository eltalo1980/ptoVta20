@extends('layouts.app')
@section('content')
    
<div class="row">
	<section class="content">
		<div class="col-md-8 col-md-offset-2">
			@if (count($errors) > 0)
			<div class="alert alert-danger">
				<strong>Error!</strong> Revise los campos obligatorios.<br><br>
				<ul>
					@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
			@endif
			@if(Session::has('success'))
			<div class="alert alert-info">
				{{Session::get('success')}}
			</div>
			@endif
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">Nueva Clase </h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
						<form method="POST" action="{{ route('clases.store') }}"  role="form">
                            @csrf
							<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
										<strong>Fecha:</strong>
                                        <input type="text" name="fecha"  class="form-control" value ="{{date("d-m-Y")}}" placeholder="Titulo Leccion">
                                        <strong>Titulo:</strong>
                                        <input type="text" name="titulo"  class="form-control" placeholder="Titulo Leccion">
                                        <strong>tiempo:</strong>
                                        <input type="number" name="duracion"  class="form-control" placeholder="10" value="10">
                                        <strong>Descripcion:</strong>
                                        <textarea rows="4", cols="20" class="form-control input-sm" id="descripcion" name="descripcion" style="resize:none, "></textarea>
										<strong>orden:</strong>
                                        <input type="number" name="orden"  class="form-control" placeholder="10" value="1">
									</div>
                                </div>
                            </div>    

							<div class="row">
 								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Guardar" class="btn btn-success btn-block">
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


