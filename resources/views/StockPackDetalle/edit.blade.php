
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
						
						<form method="PUT" name="form1" id="form1"  role="form" action="{{route('StockPack.store')}}">							
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

							<h1>Codigo<input type="text" name="codigo" id="codigo" onkeypress="fncBuscaProducto(event)" autofocus></h1>

							@if(isset($detallePack) and count($detallePack)>0)
								<table class="table table-striped">
								<thead>
									<tr>
										<th scope="col">Descripcion</th>
										<th scope="col">Codigo</th>
										<th scope="col">Cantidad</th>
										<th scope="col">Accion</th>
									</tr>
								</thead>
									<tbody>
											<tr hidden>
												<td>1</td>
												<td>2</td>
												<td>3</td>
												<td>
													<form action="{{ route('StockPackDetalle.destroy',4) }}" method="POST">
														@csrf
														@method('DELETE')
														<button type="submit" class="btn btn-danger btn-xs">Delete</button>
													</form>

												</td>                                        
											</tr>
										@foreach($detallePack as $lsPack)  
											<tr>
												<td>{{$lsPack->descripcion}}</td>
												<td>{{$lsPack->codigo}}</td>
												<td>{{$lsPack->cantidad}}</td>
												<td>
													<form action="{{ route('StockPackDetalle.destroy',$lsPack->codigo) }}" method="POST">
														@csrf
														<input type="hidden" id="codigo_pack" name="codigo_pack" value="{{ $infoPack[0]->codigo_pack }}" class="form-control" >
														<input type="hidden" id="codigo_producto" name="codigo_producto" value="{{ $lsPack->codigo }}" class="form-control" >
														@method('DELETE')
														<button type="submit" class="btn btn-danger btn-xs">Delete</button>
													</form>

												</td>                                        
											</tr>
										@endforeach 
									</tbody>
								</table>
							@endif      
	
							<div class="row">
								<!--
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Guardar" class="btn btn-success btn-block">
								</div>	
								-->
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
		console.log("Entre");
		document.getElementById("form1").action="{{ route('StockPackDetalle.store') }}";
    	document.getElementById("form1").submit();

    }

}

</script>    
