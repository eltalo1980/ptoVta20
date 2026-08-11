@extends('layouts.app')
@section('content')


<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <strong>Pagos</strong>
            <div class="row">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="card_title">
                            </span>
                            @if (Auth::user()->nivel >= 10)
                             <div class="float-right">
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
            </div>                    
            <div class="col-md-6 mb-4">
            <br>
            <form class="form-horizontal" action="{{ route('pago.update',1) }}" method="POST" name="form1">            
            @csrf
            @method('PUT')                
            @if($formaPago=='1' or $formaPago=='3' )
                <div class="row">
				    <div class="col-md-6">
                        <button type="button" id="butonAtras1" class="btn btn-warning" onclick="history.back()">Atras</button>
                        <button type="button" class="btn btn-dark btn-rounded">        </button>
                        <input type="submit" id="btn1" value="Pagar" class="btn btn-success">
                    </div>
                </div>
            @endif                                        
            @if($formaPago=='2')
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" id="butonAtras2" class="btn btn-warning" onclick="history.back()">Atras</button>
                        <button type="button" class="btn btn-dark btn-rounded">        </button>
                        <input type="submit" id="btn2" disabled value="Pagar" class="btn btn-success">
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" id="add0"   ><a href="javascript:fncAumentar(0)"   ><img src="images/billete0.jpg" ></a></button>
                        <button type="button" id="add1000"><a href="javascript:fncAumentar(1000)"><img src="images/billete1000.jpg"></a></button>
                        <button type="button" id="add2000"><a href="javascript:fncAumentar(2000)"><img src="images/billete2000.jpg"></a></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" id="add5000" ><a href="javascript:fncAumentar(5000)"><img src="images/billete5000.jpg"></a></button>
                        <button type="button" id="add10000"><a href="javascript:fncAumentar(10000)"><img src="images/billete10000.jpg"></a></button>
                        <button type="button" id="add20000"><a href="javascript:fncAumentar(20000)"><img src="images/billete20000.jpg"></a></button>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <a class="btn btn-info btn-rounded  btn-lg" href="javascript:fncAumentar(500)">500</a>
                        <a class="btn btn-success btn-rounded  btn-lg" href="javascript:fncAumentar(100)">100</a>
                        <a class="btn btn-warning btn-rounded btn-lg" href="javascript:fncAumentar(50)">50 </a>
                        <a class="btn btn-info btn-rounded btn-lg" href="javascript:fncAumentar(10)">10 </a>
                    </div>
                </div>


                

            @endif                                        
            <br>
                <!-- <form class="form-horizontal" method="POST" action="{{ route('venta.store') }}">  -->
                    @if($formaPago=='2')
                        <b>Monto Pago:</b>
                        <input type="number" name="montoSencillo" id="montoSencillo" value ="0" onkeypress="fncCalulaVuelto()" onchange="fncCalulaVuelto()">
                    @endif                        
                    <input type="hidden" name="formaPago" id="formaPago" value ="{{$formaPago}}">
                    <input type="hidden" name="codigoBorrar" id="codigoBorrar" value ="">
                    <input type="hidden" name="totalPagar" id="totalPagar" value ="{{$totalTmp}}">
                    <input type="hidden" name="totalVuelto" id="totalVuelto" value ="">

                    <div id="myDynamicTable"></div>
                    @if(isset($totalTmp) )
                        <table class="table table-striped"> 
                            <tbody>
                                <tr>
                                <td><h3>Total venta</h3></td>
                                <td id="IdTotalPagar"><h3>${{$totalTmp}}</h3></td>
                                </tr>
                                <tr>
                                <td><h3>Vuelto</h3></td>
                                <td id="IdVuelto"><h3>0</h3></td>
                                </tr>
                            </tbody>
                        </table>
                    @endif            

                    @if(isset($ventasTmp) and count($ventasTmp)>0 )
                    <table class="table table-striped"> 
                        <thead>
                            <tr>
                                <!-- <th scope="col">Codigo</th> -->
                                <th scope="col">Descripcion</th>
                                <th scope="col">Valor</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">SubTotal</th>
                            </tr>
                        </thead>
                            <tbody>
                                @foreach($ventasTmp as $vtaTmp)  
                                    <tr>
                                        <!-- <td>{{$vtaTmp->codigo}}</td> -->
                                        <td>{{trim($vtaTmp->descripcion)}}</td>
                                        <td>{{trim($vtaTmp->precio_venta)}}</td>
                                        <td>{{trim($vtaTmp->cantidad)}}</td>
                                        <td>{{trim($vtaTmp->ValorTotal)}}</td>
                                    </tr>
                                @endforeach 
                                    <tr>
                                        <td></td>
                                        <td><b>Total</b></td>
                                        <td></td>
                                        <td><b>{{$totalTmp}}</b></td>
                                    </tr>

                            </tbody>
                        </table>
                    @endif    
                    <div class="row">
                </div>
                @if($formaPago=='2')
                    <input type="submit" id="btn3" disabled value="Pagar" class="btn btn-success btn-block">
                @endif                    
            </form>
            
            <!--Section: Shadows-->
        </section>
      </section>
      <!-- Section: Components -->
    </div>
@endsection
<script language="javascript">
document.getElementById("butonPago2").disabled = true;

function fncAumentar(monto)
{
    if(monto==0)
    {
        document.getElementById("btn2").disabled = true;
        document.getElementById("btn3").disabled = true;
        document.getElementById('montoSencillo').value=0;
        document.getElementById('IdVuelto').innerHTML     =  "<h3>"+Intl.NumberFormat('es-CL', {currency: 'CLP', style: 'currency'}).format(0)+"</h3>";
    }
    else 
    {
        let montoSencillo   = parseInt(document.getElementById('montoSencillo').value);
        if (isNaN(montoSencillo)) {
            document.getElementById('montoSencillo').value=0;
        }
        montoSencillo+=monto;
        document.getElementById('montoSencillo').value=montoSencillo;
        fncCalulaVuelto();
    }

}
function fncCalulaVuelto()
{
	let totalPagar      = parseInt(document.getElementById('totalPagar').value.replace('.',''));
	let montoSencillo   = parseInt(document.getElementById('montoSencillo').value);
	let valor =(montoSencillo-totalPagar);
	document.getElementById('totalVuelto').value=valor;
    document.getElementById('IdVuelto').innerHTML     =  "<h3>"+Intl.NumberFormat('es-CL', {currency: 'CLP', style: 'currency'}).format(valor)+"</h3>";
    if(valor >=0)
    {
        document.getElementById("btn2").disabled = false;
        document.getElementById("btn3").disabled = false;
    }
    else
    {
        document.getElementById("btn2").disabled = true;
        document.getElementById("btn3").disabled = true;
    }
}

function fncRealizarPago1()
{
    let seguir=false;
    let totalPagar      = parseInt(document.getElementById('totalPagar').value.replace('.',''));
	let montoSencillo   = parseInt(document.getElementById('montoSencillo').value);
    if((montoSencillo-totalPagar)<0)
    {
        alert("Revise el Monto ingresado");
        document.getElementById('montoSencillo').focus();
    }
    else
    {
        //document.getElementById("form1").action="{{ route('pago.update',1)}}";
        //document.getElementById("form1").method="update";
        
        document.getElementById("butonPago2").disabled = false;
        
    }


}

</script>    
