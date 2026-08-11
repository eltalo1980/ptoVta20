
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
					<h3 class="panel-title">Crear Pack de Productos</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
                        <form action="{{ route('StockPack.store') }}" method="POST" name="form1">
                            @csrf
                            @method('POST')
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Cod Pack:    </strong><input minlength="3" id="codigoPack" type="text" value="" name="codigoPack" value="" class="form-control" autofocus ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Descripcion:    </strong><input minlength="3" id="descripcion" type="text" name="descripcion" value="" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio venta:   </strong><input id="precio_venta" type="money" name="precio_venta" value="0" class="form-control"  ></div></div>
								</div>
                            </div>    
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  id="btnGuardar" value="Guardar  " class="btn btn-success btn-block">
								</div>	
								<div class="col-xs-3 col-sm-3 col-md-3">
									<a href="{{ route('StockPack.index') }}" class="btn btn-danger btn-block" >Atrás</a>
								</div>	
 							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection
<script language="javascript">

</script>    
