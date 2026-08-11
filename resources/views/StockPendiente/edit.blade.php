
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
					<h3 class="panel-title">Editar Producto</h3>
				</div>
				<div class="panel-body">					
					<div class="table-container">
						
                        <form action="{{ route('stock.update',$ProductoEditar[0]->id_producto) }}" method="POST" name="form1">
                            @csrf
                            @method('PUT')
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6">
										<div class="form-group">
											<strong>Cod Producto:</strong>
											<input type="text" name="codigo" value="{{ $ProductoEditar[0]->codigo }}" class="form-control" >
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6">
										<div class="form-group">
											<strong>Description:</strong>
											<input type="text" name="descripcion" value="{{ $ProductoEditar[0]->descripcion }}" class="form-control" >
										</div>
									</div>
								</div>
								

								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Empresa:    </strong>
								<input id="empresa" type="text"  name="empresa" value="{{ $ProductoEditar[0]->empresa }}" class="form-control">
									<select  name="cmbEmpresa" id="cmbEmpresa" class="form-control " onchange="fncCambiaEmpresa()">
										@if($ProductoEditar[0]->empresa="" || $ProductoEditar[0]->empresa="- Sin Departamento -")
											@foreach($listaEmpresas as $lsEmp)
												<option value="{{$lsEmp->empresa}}">{{$lsEmp->empresa}}</option>
											@endforeach
										@else
											@foreach($listaEmpresas as $lsEmp)
												<option value="{{ $ProductoEditar[0]->empresa }}">{{ $ProductoEditar[0]->empresa }}</option>
											@endforeach
										@endif
									</select>
									</div></div>

								</div>
								<div class="row">
									<div class="col-xs-6 col-sm-6 col-md-6"><strong>Activo:    </strong>
										<select  name="cmbActivo" id="cmbActivo" class="form-control ">
											@if ($ProductoEditar[0]->activo==1)
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
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Descripcion:    </strong><input id="descripcion" type="text" name="descripcion" value="{{ $ProductoEditar[0]->descripcion }}" class="form-control"></div></div>
								</div>

								<div class="row">
								@if($ProductoEditar[0]->precio_neto_ori > 0 )
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Valor Neto:   </strong><input id="precio_neto" type="money" name="precio_neto" value="{{ $ProductoEditar[0]->precio_neto_ori }}" class="form-control" ></div></div>
									</div>
								@else
									<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Valor Neto (Calculado):   </strong><input id="precio_neto" type="money" name="precio_neto" value="{{ 
										(int)str_replace('.','',$ProductoEditar[0]->precio_costo)- ((int)str_replace('.','',$ProductoEditar[0]->precio_costo)*$iva/100)  
										}}" class="form-control" ></div></div>
									</div>
								@endif
								<div class="row">
									<input id="valorIVA" type="hidden" name="valorIVA" value="{{$iva}}">
								<div class="col-xs-6 "><div class="form-group"><strong>Agregar IVA:   </strong><input id="agregar_iva" type="checkbox" name="agregar_iva" value="" class="form-control" onkeydown="fncCalulaPrecioVenta()" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>

								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio Costo:   </strong><input id="precio_costo" type="money" name="precio_costo" value="{{ str_replace('.','',($ProductoEditar[0]->precio_costo)) }}" class="form-control" onkeydown="fncCalulaPrecioVenta()" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"></div></div>
								</div>


								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Porcentaje:   </strong><input id="porcentaje" type="number" name="porcentaje" value="{{$ganacia}}" class="form-control" onkeydown="fncCalulaPrecioVenta()" onkeypress="fncCalulaPrecioVenta()" onchange="fncCalulaPrecioVenta()"  ></div></div>
								</div>
								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>Precio venta:   </strong><input id="precio_venta" type="money" name="precio_venta" value="{{  str_replace('.','',$ProductoEditar[0]->precio_venta) }}" class="form-control" ></div></div>
								</div>
								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad:       </strong><input id="cantidad" type="text" name="cantidad" value="{{ $ProductoEditar[0]->cantidad }}" class="form-control" ></div></div>
								</div>
								<div class="row">
								<div class="col-xs-6 col-sm-6 col-md-6"><div class="form-group"><strong>cantidad_minima:</strong><input id="cantidad_minima" type="text" name="cantidad_minima" value="{{ $ProductoEditar[0]->cantidad_minima }}" class="form-control" ></div></div>
								</div>
                            </div>    
							<div class="row">
								<div class="col-xs-3 col-sm-3 col-md-3">
									<input type="submit"  value="Guardar " class="btn btn-success btn-block">
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
function fncCalulaPrecioVenta()
{
	//var precio_neto=0;
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
/*
para formatear los numeros
function fncformateaNumero1(valor)
{
	var monto = valor.replace(".","");
	alert(monto);
	let aa
		if(monto.length==3) 
        {
            aa = monto;
        } 
    
		if(monto.length==4) 
        {
            aa = monto.substring(0,1).'.'.monto.substring(1,3);
        } 
        if(monto.length==5) 
        {
			aa = monto.substring(0,2).'.'.monto.substring(2,3);

        } 
        if(monto.length==6) 
        {
			aa = monto.substring(0,3).'.'.monto.substring(3,3);
        } 
        return aa;
}

*/
</script>    
