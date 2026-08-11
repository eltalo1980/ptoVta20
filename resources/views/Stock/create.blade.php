
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
					<h3 class="panel-title">Crear Producto</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
                        <form action="{{ route('stock.store') }}" method="POST" name="form1">
                            @csrf
                            @method('POST')
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Cod Producto:    </strong><input minlength="3" id="codigo" type="text" value="{{$codigo}}" name="codigo" value="" class="form-control" autofocus ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Descripcion:    </strong><input minlength="3" id="descripcion" type="text" name="descripcion" value="{{$descripcionPorducto}}" class="form-control" ></div></div>
								</div>
								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Empresa:    </strong>
								<input id="empresa" type="text"  name="empresa" value="" class="form-control">
									<select  name="cmbEmpresa" id="cmbEmpresa" class="form-control " onchange="fncCambiaEmpresa()">
											@foreach($listaEmpresas as $lsEmp)
												<option value="{{$lsEmp->empresa}}">{{$lsEmp->empresa}}</option>
											@endforeach
									</select>
									</div></div>
								</div>

								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio neto:   </strong><input id="precio_neto" type="money" name="precio_neto" value="0" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>

								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio costo:   </strong><input id="precio_costo" type="money" name="precio_costo" value="0" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>
								<div class="row">
									<input id="valorIVA" type="hidden" name="valorIVA" value="19">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Agregar IVA:   </strong><input id="agregar_iva" type="checkbox" name="agregar_iva" value="" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Porcentaje:   </strong><input id="porcentaje" type="number" name="porcentaje" value="30" class="form-control" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"  ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio venta:   </strong><input id="precio_venta" type="money" name="precio_venta" value="0" class="form-control" onfocusout="fncHabilitaButtonGuardar()" onkeypress="fncHabilitaButtonGuardar()" onchange="fncHabilitaButtonGuardar()"  ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad:       </strong><input id="cantidad" type="number" min="0" name="cantidad" value="0" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad_minima:</strong><input id="cantidad_minima" type="number" name="cantidad_minima" value="0" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad_venta_mayor:</strong><input id="cantidad_venta_mayor" type="number" name="cantidad_venta_mayor" value="0" class="form-control" ></div></div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio venta mayor:</strong><input id="precio_venta_mayor" type="number" name="precio_venta_mayor" value="0" class="form-control" ></div></div>
								</div>

								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6">
										<div class="form-group">
											<strong>Venta por Unidad</strong>
											<select id="venta_por_unidad" name="venta_por_unidad" class="form-control">
												<option value="0">No</option>
												<option value="1">Sí</option>
											</select>
										</div>
									</div>
								</div>

                            </div>    
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  id="btnGuardar" disabled value="Guardar  " class="btn btn-success btn-block">
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
function fncCambiaEmpresa()
{
	document.getElementById("empresa").value = document.getElementById("cmbEmpresa").value
}

function fncHabilitaButtonGuardar()
{
	var_seguir=true;
	if(document.getElementById("codigo").value.length < 1){var_seguir=false;}
	if(document.getElementById("descripcion").value.length<3){var_seguir=false;}
	if(document.getElementById("precio_venta").value.length<2){var_seguir=false;}
	if(var_seguir)
	{	
		document.getElementById("btnGuardar").disabled = false;
	}
	else
	{
		document.getElementById("btnGuardar").disabled = true;
	}

}

function fncCalulaPrecioVenta()
{
	var precio_neto=0;
	var procentaje=0;
	var precio_costo=0;
	precio_neto = parseInt(document.getElementById('precio_neto').value);
	precio_costo= parseInt(document.getElementById('precio_costo').value);
	procentaje  = parseInt(document.getElementById('porcentaje').value);
	var valor=0;

	//document.getElementById('precio_costo').value = (precio_neto + (precio_costo * parseInt(document.getElementById('valorIVA').value)/100)) ;
	
	precio_costo = Math.round((precio_neto + (precio_neto * parseInt(document.getElementById('valorIVA').value)/100)));
	document.getElementById('precio_costo').value =precio_costo

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



function fncCalulaPrecioVentaOLD()
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