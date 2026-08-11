@extends('layouts.app')
@section('content')


<div class="container my-5">
      <!-- Section: Components -->
      <section class="">
        <section id="demo" class="">
          <strong>Productos</strong>
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

              <div class="col-md-6 mb-4">
                                
                <form class="form-horizontal" name="form1" id="form1" method="GET" action="{{route('StockPack.index')}}">
                    @csrf
                    Codigo / Descipcion / Emprersa
                    <div class="row">
				        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
                            <a href="{{ route('StockPack.create') }}" class="btn btn-success btn-sm">Crear</a> 
                            <input class="form-control " type="text" name="codigo" id="codigo" value="{{$codigo}}" onkeypress="fncBuscaProducto(event)" autofocus>
                            <select  name="cmbPack" id="cmbPack" class="form-control " onchange="fncCambiaPack()">
                                @foreach($listadoPack as $lsPack)
                                    <option value=""></option>
                                    @if($lsPack->codigo==0)
                                        <option value="{{$lsPack->codigo_pack}}">{{$lsPack->descripcion}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    </div>

                    @if(isset($listadoPack) and count($listadoPack)>0)
                        <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col"></th>                                
                                <th scope="col">codigo_pack</th>
                                <th scope="col">Descripcion</th>
                                <th scope="col">venta</th>
                                <th scope="col">borrar</th>
                            </tr>
                        </thead>
                            <tbody>
                                @foreach($listadoPack as $lsPack)  
                                    @if($lsPack->codigo==0)
                                        <tr class="{{$lsPack->activo==0 ? 'danger':'' }}">
                                            <td>
                                                <form action="{{ route('StockPack.edit',$lsPack->codigo_pack) }}" method="POST">   
                                                    <a href="{{ route('StockPack.edit',$lsPack->codigo_pack) }}" class="btn btn-success btn-xs pull-left">Ver</a>
                                                    <a href="{{ route('StockPackDetalle.show',$lsPack->codigo_pack) }}" class="btn btn-primary btn-xs pull-right">Productos</a>
                                                </form>
                                            </td>
                                            <td>{{$lsPack->codigo_pack}}</td>
                                            <td>{{$lsPack->descripcion}}</td>
                                            <td>{{$lsPack->precio_venta}}</td>
                                            <td>
                                                @if($lsPack->activo==1)
                                                <form action="{{ route('StockPack.destroy',$lsPack->id_pack) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                                </form>
                                                @endif
                                            </td>                                        
                                        </tr>
                                    @endif
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
//document.getElementById('codigo').focus();
function fncCambiaPack()
{
	document.getElementById("codigo").value = document.getElementById("cmbPack").value;
    document.getElementById("form1").action="{{ route('StockPack.index') }}";
    document.getElementById("form1").submit();
}


function fncProductoChangeStockPackAmount(idProducto)
{
    campo="cant_"+idProducto;
    cantidad=document.getElementById(campo).value;
/*
    document.getElementById("cantidadCambio").value =cantidad;
    document.getElementById("cantidadIdproducto").value =idProducto;
*/	
    var url = "{{ route('StockPack.update', 'idProducto|:id|cambiaStockPack|:cantidad') }}";
	url = url.replace(':id', idProducto);
    url = url.replace(':cantidad', cantidad);
	location.href = url;

}

function fncSumarProducto()
{
	document.getElementById("codigo").value = document.getElementById("cmbEmpresa").value;
   
    document.getElementById("form1").action="{{ route('StockPack.index') }}";
    document.getElementById("form1").submit();
}



function fncBuscaProducto(e)
{
    var valoresOK=false;
    if(e.keyCode === 13)
    {
        document.getElementById("form1").action="{{ route('StockPack.index') }}";
        document.getElementById("form1").submit();
    }
}

</script>    
