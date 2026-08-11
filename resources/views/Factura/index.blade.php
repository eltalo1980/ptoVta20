@extends('layouts.app')
@section('content')


<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <strong>Facturas</strong>
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
                                
                <form class="form-horizontal" name="form1" id="form1" method="GET" action="{{route('Factura.index')}}">
                    @csrf
                    <a href="{{ route('Factura.create') }}" class="btn btn-success btn-sm">Crear</a>                        
                    <div class="row">
				        <div class="col-md-6">
                            <input type="hidden" name="fechaFactura" id="fechaFactura">
                            <select  name="cmBfechaFactura" id="cmBfechaFactura" class="form-control " onchange="fncCambiaFecha()">
                                <option value=""></option>
                                @if(isset($listaFechasFacturas) and count($listaFechasFacturas)>0 )        
                                    @foreach($listaFechasFacturas as $lsFecha)
                                        <option value="{{$lsFecha->fecha_pago}}">{{$lsFecha->fecha_pago}}</option>
                                    @endforeach
                                @endif
                            </select>
                            
                        </div>
                    </div>
                    </div>
                    
                    @if(isset($listaPagos) and count($listaPagos)>0 )
                        <table class="table table-striped">
                        <thead>
                            <tr>

                                <th scope="col">Fecha_Pago</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Monto</th>
                            </tr>
                        </thead>
                            <tbody>
                                @foreach($listaPagos as $lsPag)  
                                    <tr>
                                        <td>{{$lsPag->fecha_pago}}</td>
                                        <td>{{$lsPag->empresa}}</td>
                                        <td>{{$lsPag->factura_monto}}</td>
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
<script language="javascript">
function fncCambiaFecha()
{
	document.getElementById("fechaFactura").value = document.getElementById("cmBfechaFactura").value;
    document.getElementById("form1").action="{{ route('Factura.index') }}";
    document.getElementById("form1").submit();
}


</script>    
