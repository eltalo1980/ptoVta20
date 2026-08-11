@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding: 20px 30px;">
    <div class="card-premium">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h1 style="font-weight: 900; color: var(--dark); margin: 0;">Resumen de Ventas</h1>
            <div class="status-badge" style="background: rgba(37, 117, 252, 0.1); color: var(--primary);">
                HISTORIAL DE TRANSACCIONES
            </div>
        </div>

        @if ($message = Session::get('success'))
        <div class="alert alert-success alert-premium">
            <i class="fa fa-check-circle"></i> {{ $message }}
        </div>
        @endif

        <div class="row" style="background: #f8fafc; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid var(--border);">
            <form name="form1" id="form1" method="GET" action="{{route('ResumenVenta.index')}}">
                @csrf
                <div class="col-md-6">
                    <label style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Seleccionar Fecha de Venta</label>
                    <input type="hidden" name="fecha_venta" id="fecha_venta">
                    <select name="cmBfechaVenta" id="cmBfechaVenta" class="form-control" onchange="fncCambiaFecha()" style="height: 45px; border-radius: 8px;">
                        @if(isset($listaFechas) and count($listaFechas)>0 )
                        <option value="">-- Seleccione una fecha --</option>
                        @foreach($listaFechas as $lsFecha)
                        <option value="{{$lsFecha->fecha_venta}}" {{ (request('fecha_venta') == $lsFecha->fecha_venta) ? 'selected' : '' }}>{{$lsFecha->fecha_venta}}</option>
                        @endforeach
                        @else
                        <option value="">No hay ventas registradas</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3" style="padding-top: 25px;">
                    <a href="{{ route('ResumenVenta.index') }}" class="btn btn-dark btn-rounded btn-block" style="height: 45px;">
                        Limpiar Filtros
                    </a>
                </div>
            </form>
        </div>

        @if(isset($listadoVentas) and count($listadoVentas)>0)
        <div class="table-responsive">
            <h3 style="font-weight: 800; color: var(--dark); margin-bottom: 20px;">Ventas del día seleccionado</h3>
            <table class="table-premium">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">Detalle</th>
                        <th>Cajero / Usuario</th>
                        <th>Fecha y Hora</th>
                        <th>Pago</th>
                        <th class="text-right">Monto Recibido</th>
                        <th class="text-right">Total Venta</th>
                        <th class="text-right">Vuelto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listadoVentas as $lsVtas)
                    <tr>
                        <td class="text-center">
                            <a href="{{ route('ResumenVenta.edit', ['ResumenVentum' => $lsVtas->id_ventas, 'fecha_venta' => request('fecha_venta')]) }}" class="btn btn-primary btn-xs" style="border-radius: 5px;">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                        <td><span style="font-weight: 700;">{{$lsVtas->nombres}}</span></td>
                        <td>{{$lsVtas->fecha_venta}}</td>
                        <td>
                            @if($lsVtas->forma_pago == 'Efectivo')
                            <span class="status-badge" style="background: rgba(39, 174, 96, 0.1); color: var(--success);">{{$lsVtas->forma_pago}}</span>
                            @else
                            <span class="status-badge" style="background: rgba(37, 117, 252, 0.1); color: var(--primary);">{{$lsVtas->forma_pago}}</span>
                            @endif
                        </td>
                        <td class="text-right text-muted">${{ number_format((float)($lsVtas->monto_sencillo ?? 0), 0, ',', '.') }}</td>
                        <td class="text-right">
                            <span style="font-weight: 800; color: var(--dark);">${{ number_format((float)($lsVtas->total_venta ?? 0), 0, ',', '.') }}</span>
                        </td>
                        <td class="text-right text-success" style="font-weight: 700;">${{ number_format((float)($lsVtas->vuleto ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(isset($listadoDetalle) and count($listadoDetalle)>0 )
        <div class="table-responsive" style="margin-top: 40px; border-top: 2px dashed var(--border); padding-top: 30px;">
            <h3 style="font-weight: 800; color: var(--dark); margin-bottom: 20px;">Detalle de la Venta</h3>
            <table class="table-premium">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción del Producto</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Sub-Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listadoDetalle as $lsVtasDet)
                    <tr>
                        <td><small class="text-muted">{{$lsVtasDet->codigo}}</small></td>
                        <td style="font-weight: 700;">{{$lsVtasDet->descripcion}}</td>
                        <td class="text-right">${{ number_format((float)($lsVtasDet->precio_venta ?? 0), 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="status-badge" style="background: #f1f5f9; color: #475569;">{{$lsVtasDet->cantidad}}</span>
                        </td>
                        <td class="text-right" style="font-weight: 800; color: var(--primary);">${{ number_format((float)($lsVtasDet->sub_total ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-20">
                <a href="{{ route('ResumenVenta.index', ['fecha_venta' => request('fecha_venta')]) }}" class="btn btn-danger btn-rounded">
                    <i class="fa fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
<script language="javascript">
    function fncCambiaFecha() {
        document.getElementById("fecha_venta").value = document.getElementById("cmBfechaVenta").value;
        document.getElementById("form1").action = "{{ route('ResumenVenta.index') }}";
        document.getElementById("form1").submit();
    }


    document.getElementById('codigo').focus();

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