@extends('layouts.app')
@section('content')
<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <h3 class="text-center"><strong>Ventas</strong></h3>
            <div class="row">
            <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                
                            </span>
                            @if (Auth::user()->nivel >= 10)
                             <div class="float-right">
                               <!--  <a href="{{ route('venta.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left"> -->
                                  {{ __('Ventas') }}
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

                <!--                 <form class="form-horizontal" method="POST" action="{{ route('venta.store') }}"> -->
                <form class="form-horizontal" name="form1" method="POST">
                    @csrf
                    <input type="text" name="codigo" id="codigo" onkeypress="fncBuscaProducto(event)">
                    <input type="text" name="ventaData" id="ventaData" value ="">
                    
                    <input type="text" name="ventaTotal"  id="ventaTotal"value ="">

                    



                    <table id="myTotal"></table>
                    <table id="myTable"></table>

                    <div id="myDynamicTable"></div>

                    <!-- productos seleccionados-->
                    <!-- Encuestas -->
                    @if(isset($listadoProductos) and count($listadoProductos)>0 )
                        <!-- <table class="table table-striped"> -->
                        <table hidden>
                        <thead>
                            <tr>
                                <th scope="col">Codigo</th>
                                <th scope="col">Descripcion</th>
                                <th scope="col">Precio_venta</th>
                            </tr>
                        </thead>
                            <tbody>
                                @foreach($listadoProductos as $lsProd)  
                                    <tr id="cod{{$lsProd->codigo}}">
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
      </section>
      <!-- Section: Components -->
    </div>
@endsection
<script language="javascript">
function fncBuscaProducto(e)
{
    var valoresOK=false;
    if(e.keyCode === 13)
    {
        e.preventDefault();
        let idFila = document.getElementById("cod"+document.form1.codigo.value);  // obtencion del Id del producto 
        if(idFila== null)
        {
            alert("Producto no encontrado!!!");
        }
        else
        {
            let datosVenta = idFila.getElementsByTagName("td")
            fncProductoAdd(datosVenta)
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
    myAddDataTable(datosVenta[0].innerHTML, datosVenta[1].innerHTML, datosVenta[2].innerHTML, arrVentas.length-1);
    myAddDataTableTotal(total,arrVentas.length);
    
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
