@extends('layouts.app')
@section('content')


<div class="container-fluid" style="padding: 20px 30px;">
    <div class="card-premium">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h1 style="font-weight: 900; color: var(--dark); margin: 0;">Gestión de Packs</h1>
            <a href="{{ route('StockPack.create') }}" class="btn btn-success btn-rounded">
                <i class="fa fa-plus"></i> Nuevo Pack
            </a>
        </div>

        @if(isset($Mensaje) and strlen($Mensaje) > 0)
        <div class="{{$Estilo}} alert-premium">
            <strong>{{$Mensaje}}</strong>
        </div>
        @endif

        <div class="row" style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border);">
            <form name="form1" id="form1" method="GET" action="{{route('StockPack.index')}}">
                @csrf
                <div class="col-md-6">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Buscar por Código o Descripción</label>
                    <div class="input-group">
                        <span class="input-group-addon" style="background: white; border-right: none;"><i class="fa fa-search"></i></span>
                        <input class="form-control" type="text" name="codigo" id="codigo" value="{{$codigo}}" onkeypress="fncBuscaProducto(event)" placeholder="Escriba aquí..." style="border-left: none; height: 45px; border-radius: 0 8px 8px 0;" autofocus>
                    </div>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Filtrar por Pack</label>
                    <select name="cmbPack" id="cmbPack" class="form-control" onchange="fncCambiaPack()" style="height: 45px; border-radius: 8px;">
                        <option value="">Todos los packs</option>
                        @foreach($listadoPack as $lsPK)
                        @if($lsPK->codigo==0)
                        <option value="{{$lsPK->codigo_pack}}" {{ $codigo == $lsPK->codigo_pack ? 'selected' : '' }}>{{$lsPK->descripcion}}</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" style="padding-top: 25px;">
                    <button type="submit" class="btn btn-primary btn-rounded btn-block" style="height: 45px;">
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        @if(isset($listadoPack) and count($listadoPack)>0)
        <table class="table-premium">
            <thead>
                <tr>
                    <th scope="col" class="text-center">Acciones</th>
                    <th scope="col">Cod Pack</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Venta</th>
                    <th scope="col" class="text-center">Stock</th>
                    <th scope="col" class="text-center">Mín.</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listadoPack as $lsPack)
                @if($lsPack->codigo==0)
                @php
                $trClass = '';
                if ($lsPack->activo == 0) {
                $trClass = 'danger';
                } elseif ((int)$lsPack->cantidad <= (int)$lsPack->cantidad_minima) {
                    $trClass = 'warning';
                    } else {
                    $trClass = 'success';
                    }
                    @endphp
                    <tr class="{{ $trClass }}">
                        <td class="text-center">
                            <form action="{{ route('StockPack.edit',$lsPack->codigo_pack) }}" method="POST">
                                <a href="{{ route('StockPack.edit',$lsPack->codigo_pack) }}" class="btn btn-success btn-xs">Editar</a>
                                <a href="{{ route('StockPackDetalle.show',$lsPack->codigo_pack) }}" class="btn btn-primary btn-xs">Detalle</a>
                            </form>
                        </td>
                        <td>{{$lsPack->codigo_pack}}</td>
                        <td>{{$lsPack->descripcion}}</td>
                        <td>${{ number_format((float)$lsPack->precio_venta, 0, ',', '.') }}</td>
                        <td class="text-center">{{$lsPack->cantidad}}</td>
                        <td class="text-center">
                            <span class="status-badge" style="background: #f1f5f9; color: #475569;">{{$lsPack->cantidad_minima}}</span>
                        </td>
                        <td>
                            @if($lsPack->activo==1)
                            <form action="{{ route('StockPack.destroy',$lsPack->id_pack) }}" method="POST" onsubmit="return confirm('¿Eliminar pack?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Eliminar</button>
                            </form>
                            @else
                            <span class="text-danger">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endforeach </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
<script language="javascript">
    //document.getElementById('codigo').focus();
    function fncCambiaPack() {
        document.getElementById("codigo").value = document.getElementById("cmbPack").value;
        document.getElementById("form1").action = "{{ route('StockPack.index') }}";
        document.getElementById("form1").submit();
    }


    function fncProductoChangeStockPackAmount(idProducto) {
        campo = "cant_" + idProducto;
        cantidad = document.getElementById(campo).value;
        /*
            document.getElementById("cantidadCambio").value =cantidad;
            document.getElementById("cantidadIdproducto").value =idProducto;
        */
        var url = "{{ route('StockPack.update', 'idProducto|:id|cambiaStockPack|:cantidad') }}";
        url = url.replace(':id', idProducto);
        url = url.replace(':cantidad', cantidad);
        location.href = url;

    }

    function fncSumarProducto() {
        document.getElementById("codigo").value = document.getElementById("cmbEmpresa").value;

        document.getElementById("form1").action = "{{ route('StockPack.index') }}";
        document.getElementById("form1").submit();
    }



    function fncBuscaProducto(e) {
        var valoresOK = false;
        if (e.keyCode === 13) {
            document.getElementById("form1").action = "{{ route('StockPack.index') }}";
            document.getElementById("form1").submit();
        }
    }
</script>