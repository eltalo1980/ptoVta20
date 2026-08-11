
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
					<h3 class="panel-title">Editar Local</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
						
                        <form action="{{ route('Locales.update',$localEditar[0]->id_local) }}" method="POST" name="form1">
                            @csrf
                            @method('PUT')
								
								<input id="idLocal" type="hidden" name="idLocal" value="{{ $localEditar[0]->id_local }}">

								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><strong>Activo:    </strong>
										<select  name="cmbActivo" id="cmbActivo" class="form-control ">
											@if ($localEditar[0]->activo==1)
												<option selected value="1">Activo</option>
												<option value="0">Inactivo</option>
											@else
												<option value="1">Activo</option>
												<option selected value="0">Inactivo</option>
											@endif
										</select>
									</div>
								</div>

								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Nombre Local:    </strong><input id="descripcion" type="text" name="descripcion" value="{{ $localEditar[0]->nombre_local }}" class="form-control" ></div></div>
								</div>
								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Fecha Termino:   </strong><input id="nombre_local" type="date" name="nombre_local" value="{{ $localEditar[0]->fecha_expiracion }}" class="form-control"></div></div>
								</div>
								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Valor Plan:   </strong><input id="valor_plan" type="money" name="valor_plan" value="{{$localEditar[0]->valor_plan}}" class="form-control"></div></div>
								</div>

                            </div>    
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Guardar " class="btn btn-success btn-block">
								</div>	
								<div class="col-xs-3 col-sm-3 col-md-3">
									<a href="{{ route('Locales.index') }}" class="btn btn-danger btn-block" >Atrás</a>
								</div>	
 							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
