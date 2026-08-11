@extends('layouts.app')
@section('content')


<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <strong>Ventas {{Auth::user()->nombres}} {{Auth::user()->apellidos}}</strong>
            <div class="row">
                    <div class="card-header">
                        @if(isset($Mensaje) and strlen($Mensaje) > 0)
                            <div class="{{$Estilo}}">
                                <strong>{{$Mensaje}}</strong>
                                <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                                </ul>
                            </div>
                        @endif

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
            @if(isset($totalTmp) )
                <a class="btn btn-info btn-rounded btn-lg" href="javascript:fncFormapago(1)">Debito o Tarjeta</a>
                <button type="button" class="btn btn-dark btn-rounded">        </button>
                <a class="btn btn-success btn-rounded  btn-lg" href="javascript:fncFormapago(2)">Efectivo</a>
            @endif            

            <br>
            <br>
                <!-- <form class="form-horizontal" method="POST" action="{{ route('venta.store') }}">  -->
                
                <form class="form-horizontal" name="form1" id="form1" method="POST" action="">
                    @csrf
                    <h1>Codigo<input type="text" name="codigo" id="codigo" onkeypress="fncBuscaProducto(event)" autofocus></h1>

                    <input type="hidden" name="accion" id="accion">
                    <!-- <button type="button" class="btn btn-success"><a href="fncBuscaProducto(13)">Buscar</a></button> -->
                    <input type="hidden" name="formaPago" id="formaPago" value ="">
                    <input type="hidden" name="ventaIdProducto" id="ventaIdProducto" value ="">
                    <input type="hidden" name="ventaNombreProducto" id="ventaNombreProducto" value ="">
                    <input type="hidden" name="ventaValorProducto" id="ventaValorProducto" value ="">

                    <table id="myTotal"></table>
                    <table id="myTable"></table>

                    <div id="myDynamicTable"></div>
                    @if(isset($totalTmp) )
                        <table class="table table-striped"> 
                            <tbody>
                                <tr>
                                <td><h1>Total venta</h1></td>
                                <td><h1>{{$totalTmp}}</h1></td>
                                </tr>
                            </tbody>
                        </table>
                    @endif            


                    @if(isset($ventaTmp) and count($ventaTmp)>0 )
                    <table class="table table-bordered"> 
                        <thead>
                            <tr>
                                <th scope="col">Descripcion</th>
                                <th scope="col">Stock</th>                                
                                <th scope="col">-</th>                                
                                <th scope="col">Cant</th>
                                <th scope="col">+</th>
                                <th scope="col">Valor</th>                                
                                <th scope="col">Sub Total</th>
                                <th scope="col">Accion</th>
                            </tr>
                        </thead>
                            <tbody>
                                @foreach($ventaTmp as $vtaTmp)  
                                    <tr>
                                        <td><h3>{{$vtaTmp->descripcion}}</h3></td>
                                        <td><h1>{{$vtaTmp->stock}}</h1></td>
                                        <td>
                                            @if($vtaTmp->cantidad > 1)
                                            <a class="btn btn-danger  btn-lg" href="javascript:fncBorrarProducto('{{$vtaTmp->codigo}}')">-</a>
                                            @endif
                                        </td>
                                        <td><h1>{{$vtaTmp->cantidad}}</h1></td>
                                        <td>
                                            @if(stripos($vtaTmp->codigo,"21000")=== false)
                                            <a class="btn btn-success  btn-lg" href="javascript:fncSumarProducto('{{$vtaTmp->codigo}}')">+</a>
                                            @endif
                                        </td>
                                        <td><h1>{{$vtaTmp->precio_venta_ori}}</h1></td>
                                        <td><h1>{{$vtaTmp->precio_venta}}</h1></td>
                                        <td>
                                            <form action="{{ route('venta.destroy',$vtaTmp->codigo) }}" method="POST">
                                                <a class="btn btn-danger  btn-lg" href="{{ route('venta.destroy',$vtaTmp->codigo) }}">Eliminar</a>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach 
                            </tbody>
                        </table>
                    @endif    

                    <!-- Listado -->
                    
                    @if(isset($listadoProductos) and count($listadoProductos)>0 )
                        <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Add</th>
                                <th scope="col">Codigo</th>
                                <th scope="col">Descripcion</th>
                                <th scope="col">Precio_venta</th>
                            </tr>
                        </thead>
                            <tbody>
                                @foreach($listadoProductos as $lsProd)  
                                    <tr id="cod{{$lsProd->codigo}}">
                                    <td>
                                        <input class="btn btn-success btn-xs pull-right  btn-lg" id="clickMe" type="button" value="+" onclick="fncAceptaProducto('{{$lsProd->codigo}}');" />
                                    </td>                                        
                                    <td>{{$lsProd->codigo}}</td>
                                    <td>{{$lsProd->descripcion}}</td>
                                    <td>{{$lsProd->precio_venta}}</td>
                                    </tr>
                                @endforeach 
                            </tbody>
                        </table>
                    @endif            
   
            </form>

            <!--Section: Shadows-->
        </section>
      <!-- Section: Components -->
    </div>
@endsection
<script language="javascript">
document.getElementById('codigo').focus();

function fncAceptaProducto(codigo)
{
    document.getElementById("codigo").value=codigo;
    document.getElementById("form1").action="{{ route('venta.store') }}";
    document.getElementById("form1").submit();
}

function fncFormapago(forma)
{
    document.getElementById('formaPago').value=forma;
    document.getElementById("form1").action="{{ route('pago.index')}}";
    document.getElementById("form1").submit();
}
function fncSumarProducto(producto)
{
    document.getElementById("codigo").value=producto;
    document.getElementById("accion").value="addProducto";
    document.getElementById("form1").action="{{ route('venta.store') }}";
    document.getElementById("form1").submit();
}
function fncBorrarProducto(producto)
{
    document.getElementById("codigo").value=producto;
    document.getElementById("accion").value="delProducto";
    document.getElementById("form1").action="{{ route('venta.store') }}";
    document.getElementById("form1").submit();
}

function fncBuscaProducto(e)
{
    var valoresOK=false;
    if(e.keyCode === 13)
    {
        document.getElementById("form1").action="{{ route('venta.store') }}";
        document.getElementById("form1").submit();
    }

}


function fncBuscaProducto1(e)
{
    var valoresOK=false;

    if(e.keyCode === 42)
    {
        alert("Tarjeta Devito");
    }
    if(e.keyCode === 43)
    {
        alert("Efectivo");
    }

    if(e.keyCode === 13)
    {
        e.preventDefault();
        let idFila="";
        if(document.form1.codigo.value.substr(0,5)=="21000")
        {
            //console.log("cod"+document.form1.codigo.value.substr(0,7));
            let idFila = document.getElementById("cod"+document.form1.codigo.value.substr(0,7));  // obtencion del Id del producto 
            let datosVenta = idFila.getElementsByTagName("td")
            document.getElementById('ventaIdProducto').value=datosVenta[0].innerHTML;
            document.getElementById('ventaNombreProducto').value=datosVenta[1].innerHTML+"("+parseInt(datosVenta[2].innerHTML)+") ("+parseInt(document.form1.codigo.value.substr(7,5))/1000+"0)";
            document.getElementById('ventaValorProducto').value=Math.round(parseInt(document.form1.codigo.value.substr(7,5))*parseInt(datosVenta[2].innerHTML)/1000);
            document.getElementById("form1").action="{{ route('venta.store') }}";
            document.getElementById("form1").submit();
        }
        else
        {
            let idFila = document.getElementById("cod"+document.form1.codigo.value);  // obtencion del Id del producto 
            if(idFila== null)
            {
                alert("Producto no encontrado!!!");
            }
            else
            {
                let datosVenta = idFila.getElementsByTagName("td")
                //console.log(datosVenta);
                document.getElementById('ventaIdProducto').value=datosVenta[0].innerHTML;
                document.getElementById('ventaNombreProducto').value=datosVenta[1].innerHTML;
                document.getElementById('ventaValorProducto').value=datosVenta[2].innerHTML;
                document.getElementById("form1").action="{{ route('venta.store') }}";
                document.getElementById("form1").submit();
                //fncProductoAdd(datosVenta)
            }
        }        
        document.getElementById('codigo').value="";
    }
}

function fncProductoAdd(datosVenta)
{
    if (typeof arrVentas == 'undefined' ) 
    {
        var arrVenta = [];
        arrVentas = [[datosVenta[0].innerHTML,datosVenta[1].innerHTML,datosVenta[2].innerHTML,true]];
    }
    else
    {
        arrVentas.push([datosVenta[0].innerHTML,datosVenta[1].innerHTML,datosVenta[2].innerHTML,true]);
    }

    if (typeof total == 'undefined' ) 
    {
        var total=0;
    }
    for (i=0; i<(arrVentas.length) ; i++)
    {
        if(arrVentas[i][2].indexOf(".")>0)
        {
            document.getElementById("myDynamicTable").deleteRow(i);
            total = total + parseInt(arrVentas[i][2].substring(0,arrVentas[i][2].indexOf(".")));
        }
        else
        {
            total = total + parseInt(arrVentas[i][2]);
        }
    }
    //myAddDataTable(datosVenta[0].innerHTML, datosVenta[1].innerHTML, datosVenta[2].innerHTML, arrVentas.length-1);
    document.getElementById('ventaProducto').value=datosVenta[0].innerHTML;
    //myAddDataTableTotal(total,arrVentas.length);
    
}

function myAddDataTable(codigo,descipcion,valor,id) 
{

  document.getElementById('ventaData').value = document.getElementById('ventaData').value+'&'+codigo ;  
  var table = document.getElementById("myTable");
  var row = table.insertRow(0);
  var cell1 = row.insertCell(0);
  var cell2 = row.insertCell(1);
  var cell3 = row.insertCell(2);
  var cell4 = row.insertCell(3);  
  var cell5 = row.insertCell(4);  
  cell1.innerHTML = codigo;
  cell2.innerHTML = descipcion;
  cell3.innerHTML = valor;
  cell4.innerHTML = "<button onclick='myDeleteFunction("+id+","+valor+")'>Borrar</button>";
  cell5.innerHTML = id;
}
function myAddDataTableTotal(totalVtas,veces) 
{
    if(parseInt(veces)>1)
    {
        document.getElementById("myTotal").deleteRow(0);
    }
    var table = document.getElementById("myTotal");
    var row = table.insertRow(0);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    cell1.innerHTML = "Total a Pagar";
    cell2.innerHTML = Intl.NumberFormat('es-CL', {currency: 'CLP', style: 'currency'}).format(totalVtas);
    document.getElementById('ventaTotal').value=totalVtas;  
}
function myDeleteFunction(id,valor) 
{
    document.getElementById("myTable").deleteRow(id); // borro regitro seleccionado
    alert(id);
    myDeleteDataTableTotal(valor);
}
function myDeleteDataTableTotal(valor) 
{
    let valorActual = document.getElementById('ventaTotal').value;
    let totalVtas = valorActual - valor;
    document.getElementById("myTotal").deleteRow(0); // borro el unico registro
    var table = document.getElementById("myTotal");
    var row = table.insertRow(0);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    cell1.innerHTML = "Total a Pagar";
    cell2.innerHTML = Intl.NumberFormat('es-CL', {currency: 'CLP', style: 'currency'}).format(totalVtas);
    document.getElementById('ventaTotal').value=totalVtas;  
}


</script>    
