
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
					<h3 class="panel-title">Editar Pack de Producto</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
						
						<form method="PUT" name="form1" action="{{ route('StockPack.show','accion=|ActualizarPack|parametros=codigoPack|'.$infoPack[0]->codigo_pack.'') }}"  role="form">							
							{{ csrf_field() }}
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6">
										<div class="form-group">
											<strong>Cod Pack:</strong>
											<input type="text" disabled id="codigo_pack_ver" name="codigo_pack_ver" value="{{ $infoPack[0]->codigo_pack }}" class="form-control" >
											<input type="hidden" id="codigo_pack" name="codigo_pack" value="{{ $infoPack[0]->codigo_pack }}" class="form-control" >
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6">
										<div class="form-group">
											<strong>Description:</strong>
											<input type="text" id="descripcion_pack" name="descripcion_pack" value="{{ $infoPack[0]->descripcion }}" class="form-control" >
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6">
										<div class="form-group">
											<strong>Precio Venta:</strong>
											<input type="number" name="valor_pack" value="{{ $infoPack[0]->precio_venta }}" class="form-control" >
										</div>
									</div>
								</div>
                            </div>    

	
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Guardar" class="btn btn-success btn-block">
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
function fncBuscaProducto(e)
{
    var valoresOK=false;
    if(e.keyCode === 13)
    {
        document.getElementById("form1").action="{{ route('StockPack.store') }}";
        document.getElementById("form1").submit();
    }

}

</script>    
