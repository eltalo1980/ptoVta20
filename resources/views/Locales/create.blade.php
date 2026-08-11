
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
					<h3 class="panel-title">Create Producto</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
						
                        <form action="{{ route('stock.store') }}" method="POST" name="form1">
                            @csrf
                            @method('POST')
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Cod Producto:    </strong><input id="codigo" type="text" name="codigo" value="" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Descripcion:    </strong><input id="descripcion" type="text" name="descripcion" value="" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio costo:   </strong><input id="precio_costo" type="money" name="precio_costo" value="" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>
								<div class="row">
									<input id="valorIVA" type="hidden" name="valorIVA" value="19">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Agregar IVA:   </strong><input id="agregar_iva" type="checkbox" name="agregar_iva" value="" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Porcentaje:   </strong><input id="porcentaje" type="number" name="porcentaje" value="30" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"  ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio venta:   </strong><input id="precio_venta" type="money" name="precio_venta" value="" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad:       </strong><input id="cantidad" type="text" name="cantidad" value="" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad_minima:</strong><input id="cantidad_minima" type="text" name="cantidad_minima" value="" class="form-control" ></div></div>
								</div>
                            </div>    
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Guardar  " class="btn btn-success btn-block">
								</div>	
								<div class="col-xs-3 col-sm-3 col-md-3">
									<a href="{{ route('stock.index') }}" class="btn btn-danger btn-block" >Atrás</a>
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
function fncCalulaPrecioVenta()
{
	let precio_costo = parseInt(document.getElementById('precio_costo').value);
	let procentaje   = parseInt(document.getElementById('porcentaje').value);
	var valor=0;
	
	if(document.getElementById('agregar_iva').checked)
	{
		valor =Math.round((precio_costo+((precio_costo*procentaje)/100) + (precio_costo*(parseInt(document.getElementById('valorIVA').value))/100)));
	}
	else
	{
		 valor =Math.round((precio_costo+((precio_costo*procentaje)/100)));
	}
	
	
	document.getElementById('precio_venta').value=valor;
}
</script>    
