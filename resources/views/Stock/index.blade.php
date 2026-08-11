@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding: 20px 30px;">
    <div class="card-premium">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h1 style="font-weight: 900; color: var(--dark); margin: 0;">Gestión de Productos</h1>
            <a href="{{ route('stock.create') }}" class="btn btn-success btn-rounded">
                <i class="fa fa-plus"></i> Nuevo Producto
            </a>
        </div>

        @if(isset($Mensaje) and strlen($Mensaje) > 0)
        <div class="{{$Estilo}} alert-premium">
            <strong>{{$Mensaje}}</strong>
        </div>
        @endif

        <div class="row" style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border);">
            <form name="form1" id="form1" method="GET" action="{{route('stock.index')}}">
                @csrf
                <div class="col-md-5">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Buscar por Código o Descripción</label>
                    <div class="input-group">
                        <span class="input-group-addon" style="background: white; border-right: none;"><i class="fa fa-search"></i></span>
                        <input class="form-control" type="text" name="codigo" id="codigo" value="{{ old('codigo', $codigo ?? '') }}" onkeypress="fncBuscaProducto(event)" placeholder="Escriba aquí..." style="border-left: none; height: 45px; border-radius: 0 8px 8px 0;">
                    </div>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Filtrar por Empresa</label>
                    <select name="cmbEmpresa" id="cmbEmpresa" class="form-control" onchange="fncCambiaEmpresa()" style="height: 45px; border-radius: 8px;">
                        <option value="">Todas las empresas</option>
                        @foreach($listaEmpresas as $lsEmp)
                        <option value="{{$lsEmp->empresa}}" {{ ($codigo ?? '') == $lsEmp->empresa ? 'selected' : '' }}>{{$lsEmp->empresa}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" style="padding-top: 25px;">
                    <button type="submit" class="btn btn-primary btn-rounded btn-block" style="height: 45px;">
                        Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        @if(isset($listadoProductosStock) and count($listadoProductosStock) > 0)
        <div class="table-responsive">
            <table class="table-premium">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Producto / Código</th>
                        <th>Empresa</th>
                        <th class="text-right">Precio Neto</th>
                        <th class="text-right">Costo</th>
                        <th class="text-right">Venta</th>
                        <th class="text-center">Stock Actual</th>
                        <th class="text-center">Min.</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listadoProductosStock as $lsProd)
                    @php
                    $trClass = '';
                    if ($lsProd->activo == 0) {
                    $trClass = 'danger';
                    } elseif ((int)$lsProd->cantidad <= (int)$lsProd->cantidad_minima) {
                        $trClass = 'warning';
                        } else {
                        $trClass = 'success';
                        }
                        @endphp
                        <tr class="{{ $trClass }}">
                            <td class="text-center">
                                <a href="{{ route('stock.edit',$lsProd->id_producto) }}" class="btn btn-info btn-xs" style="border-radius: 5px;">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);">{{$lsProd->descripcion}}</div>
                                <small class="text-muted">{{$lsProd->codigo}}</small>
                                @if($lsProd->venta_por_unidad == 1)
                                <span class="status-badge" style="background: rgba(37, 117, 252, 0.1); color: var(--primary); padding: 2px 6px;">Unidad</span>
                                @endif
                            </td>
                            <td><span class="text-muted">{{$lsProd->empresa}}</span></td>
                            <td class="text-right">
                                <span style="font-weight: 600;">${{ number_format((float)($lsProd->precio_neto_ori > 0 ? $lsProd->precio_neto_ori : $lsProd->precio_neto), 0, ',', '.') }}</span>
                            </td>
                            <td class="text-right text-muted">${{ number_format((float)($lsProd->precio_costo ?? 0), 0, ',', '.') }}</td>
                            <td class="text-right">
                                <span style="font-weight: 800; color: var(--success); font-size: 16px;">${{ number_format((float)($lsProd->precio_venta ?? 0), 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                    <input type="number" id="cant_{{$lsProd->id_producto}}" value="{{ $lsProd->cantidad }}" class="form-control" style="width: 70px; text-align: center; height: 32px; border-radius: 5px;">
                                    <button onclick="fncProductoChangeStockAmount('{{$lsProd->id_producto}}')" class="btn btn-success btn-xs" title="Guardar Stock">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="status-badge" style="background: #f1f5f9; color: #475569;">{{$lsProd->cantidad_minima}}</span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <button onclick="fncProductoAddPendingAmount('{{$lsProd->id_producto}}')" class="btn btn-warning btn-xs" title="Añadir a Pendiente">
                                        <i class="fa fa-clock-o"></i>
                                    </button>
                                    @if($lsProd->activo==1)
                                    <form action="{{ route('stock.destroy',$lsProd->id_producto) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-20 text-muted small">
            <i class="fa fa-info-circle"></i> (*) Los valores resaltados corresponden a configuraciones activas.
        </div>
        @else
        <div class="text-center" style="padding: 50px;">
            <i class="fa fa-search fa-3x text-muted" style="opacity: 0.3; margin-bottom: 15px;"></i>
            <h4 class="text-muted">No se encontraron productos con los filtros aplicados</h4>
        </div>
        @endif
    </div>
</div>
@endsection
<script language="javascript">
    //document.getElementById('codigo').focus();

    function fncProductoAddPendingAmount(idProducto) {
        campo = "cant_" + idProducto;
        cantidad = document.getElementById(campo).value;
        codigo = document.getElementById("codigo").value;
        var url = "{{ route('StockPendiente.show', '|addCantidad|:idProd|:cantidad|:empresa') }}";
        url = url.replace(':idProd', idProducto);
        url = url.replace(':cantidad', cantidad);
        url = url.replace(':empresa', codigo);
        location.href = url;
    }

    function fncProductoChangeStockAmount(idProducto) {
        campo = "cant_" + idProducto;
        cantidad = document.getElementById(campo).value;
        /*
            document.getElementById("cantidadCambio").value =cantidad;
            document.getElementById("cantidadIdproducto").value =idProducto;
        */
        var url = "{{ route('stock.update', 'idProducto|:id|cambiastock|:cantidad') }}";
        url = url.replace(':id', idProducto);
        url = url.replace(':cantidad', cantidad);
        location.href = url;

    }

    function fncSumarProducto() {
        document.getElementById("codigo").value = document.getElementById("cmbEmpresa").value;
        document.getElementById("form1").action = "{{ route('stock.index') }}";
        document.getElementById("form1").submit();
    }

    function fncCambiaEmpresa() {
        document.getElementById("codigo").value = document.getElementById("cmbEmpresa").value;
        document.getElementById("form1").action = "{{ route('stock.index') }}";
        document.getElementById("form1").submit();
    }


    function fncBuscaProducto(e) {
        var valoresOK = false;
        if (e.keyCode === 13) {
            document.getElementById("form1").action = "{{ route('stock.index') }}";
            document.getElementById("form1").submit();
        }
    }


    function fncBuscaProducto1(e) {
        var valoresOK = false;
        if (e.keyCode === 42) {
            alert("Tarjeta Devito");
        }
        if (e.keyCode === 43) {
            alert("Efectivo");
        }
        if (e.keyCode === 13) {
            e.preventDefault();
            let idFila = "";
            if (document.form1.codigo.value.substr(0, 5) == "21000") {
                //console.log("cod"+document.form1.codigo.value.substr(0,7));
                let idFila = document.getElementById("cod" + document.form1.codigo.value.substr(0, 7)); // obtencion del Id del producto 
                let datosVenta = idFila.getElementsByTagName("td")

                document.getElementById('ventaIdProducto').value = datosVenta[0].innerHTML;
                document.getElementById('ventaNombreProducto').value = datosVenta[1].innerHTML + "(" + parseInt(datosVenta[2].innerHTML) + ") (" + parseInt(document.form1.codigo.value.substr(7, 5)) / 1000 + "0)";
                document.getElementById('ventaValorProducto').value = Math.round(parseInt(document.form1.codigo.value.substr(7, 5)) * parseInt(datosVenta[2].innerHTML) / 1000);
                document.getElementById("form1").action = "{{ route('venta.store') }}";
                document.getElementById("form1").submit();
            } else {
                let idFila = document.getElementById("cod" + document.form1.codigo.value); // obtencion del Id del producto 

                if (idFila == null) {
                    alert("Producto no encontrado!!!");
                } else {
                    let datosVenta = idFila.getElementsByTagName("td")
                    //console.log(datosVenta);
                    document.getElementById('ventaIdProducto').value = datosVenta[0].innerHTML;
                    document.getElementById('ventaNombreProducto').value = datosVenta[1].innerHTML;
                    document.getElementById('ventaValorProducto').value = datosVenta[2].innerHTML;
                    document.getElementById("form1").action = "{{ route('venta.store') }}";
                    document.getElementById("form1").submit();
                    //fncProductoAdd(datosVenta)
                }
            }
            document.getElementById('codigo').value = "";
        }
    }

    function fncProductoAdd(datosVenta) {
        if (typeof arrVentas == 'undefined') {
            var arrVenta = [];
            arrVentas = [
                [datosVenta[0].innerHTML, datosVenta[1].innerHTML, datosVenta[2].innerHTML, true]
            ];
        } else {
            arrVentas.push([datosVenta[0].innerHTML, datosVenta[1].innerHTML, datosVenta[2].innerHTML, true]);
        }

        if (typeof total == 'undefined') {
            var total = 0;
        }
        for (i = 0; i < (arrVentas.length); i++) {
            if (arrVentas[i][2].indexOf(".") > 0) {
                document.getElementById("myDynamicTable").deleteRow(i);
                total = total + parseInt(arrVentas[i][2].substring(0, arrVentas[i][2].indexOf(".")));
            } else {
                total = total + parseInt(arrVentas[i][2]);
            }
        }
        //myAddDataTable(datosVenta[0].innerHTML, datosVenta[1].innerHTML, datosVenta[2].innerHTML, arrVentas.length-1);
        document.getElementById('ventaProducto').value = datosVenta[0].innerHTML;
        //myAddDataTableTotal(total,arrVentas.length);

    }

    function myAddDataTable(codigo, descipcion, valor, id) {

        document.getElementById('ventaData').value = document.getElementById('ventaData').value + '&' + codigo;
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
        cell4.innerHTML = "<button onclick='myDeleteFunction(" + id + "," + valor + ")'>Borrar</button>";
        cell5.innerHTML = id;
    }

    function myAddDataTableTotal(totalVtas, veces) {
        if (parseInt(veces) > 1) {
            document.getElementById("myTotal").deleteRow(0);
        }
        var table = document.getElementById("myTotal");
        var row = table.insertRow(0);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);
        cell1.innerHTML = "Total a Pagar";
        cell2.innerHTML = Intl.NumberFormat('es-CL', {
            currency: 'CLP',
            style: 'currency'
        }).format(totalVtas);
        document.getElementById('ventaTotal').value = totalVtas;
    }

    function myDeleteFunction(id, valor) {
        document.getElementById("myTable").deleteRow(id); // borro regitro seleccionado
        alert(id);
        myDeleteDataTableTotal(valor);
    }

    function myDeleteDataTableTotal(valor) {
        let valorActual = document.getElementById('ventaTotal').value;
        let totalVtas = valorActual - valor;
        document.getElementById("myTotal").deleteRow(0); // borro el unico registro
        var table = document.getElementById("myTotal");
        var row = table.insertRow(0);
        var cell1 = row.insertCell(0);
        var cell2 = row.insertCell(1);
        cell1.innerHTML = "Total a Pagar";
        cell2.innerHTML = Intl.NumberFormat('es-CL', {
            currency: 'CLP',
            style: 'currency'
        }).format(totalVtas);
        document.getElementById('ventaTotal').value = totalVtas;
    }
</script>