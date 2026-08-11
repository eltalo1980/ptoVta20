@extends('layouts.app')
@section('content')
<script>
    window.AppConfig = {
        cajaPagadora: @json($cajaPagadora ?? 0),
        userNivel: @json($userNivel ?? 0)
    };
</script>
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Lato', sans-serif;
    }

    .vta-container {
        padding: 30px 15px;
    }

    .vta-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .vta-header {
        border-bottom: 2px solid #edeff2;
        padding-bottom: 15px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .vta-title {
        font-size: 24px;
        font-weight: 800;
        color: #2c3e50;
        margin: 0;
    }

    /* Input de Código Estilizado */
    .codigo-container {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        border: 2px dashed #cbd5e0;
        margin-bottom: 30px;
        text-align: center;
    }

    .codigo-label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    #codigo {
        width: 100%;
        max-width: 500px;
        height: 60px;
        font-size: 32px;
        font-weight: 700;
        text-align: center;
        border: 2px solid #3498db;
        border-radius: 10px;
        color: #2c3e50;
        box-shadow: 0 4px 10px rgba(52, 152, 219, 0.1);
        transition: all 0.3s ease;
    }

    #codigo:focus {
        outline: none;
        border-color: #2980b9;
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.2);
    }

    /* Tablas Premium */
    .table-vta {
        margin-bottom: 0;
    }

    .table-vta thead th {
        background-color: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px !important;
    }

    .table-vta tbody td {
        padding: 15px 12px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9;
        font-size: 18px;
    }

    .table-vta tr:last-child td {
        border-bottom: none;
    }

    .price-tag {
        font-weight: 800;
        color: #27ae60;
        font-size: 22px;
    }

    .stock-badge {
        background: #e2e8f0;
        color: #475569;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
    }

    /* Totales */
    .total-box {
        background: #2c3e50;
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .total-row:last-child {
        margin-bottom: 0;
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .total-label {
        font-size: 16px;
        font-weight: 400;
        opacity: 0.8;
    }

    .total-value {
        font-size: 28px;
        font-weight: 800;
    }

    .devolucion-value {
        color: #ff7675;
    }

    /* Mensajes Flotantes Premium */
    .fixed-alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        width: 320px;
    }

    .alert-premium {
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        padding: 15px 20px !important;
        margin-bottom: 10px !important;
        font-size: 13px !important;
        animation: slideInPremium 0.3s ease-out;
    }

    @keyframes slideInPremium {
        from {
            transform: translateX(120%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>

<div class="container-fluid vta-container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <!-- Contenedor de Mensajes Fijo -->
            <div class="fixed-alert-container">
                @if(isset($Mensaje) and strlen($Mensaje) > 0)
                <div class="{{$Estilo}} alert-premium alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="top: -2px;"><span aria-hidden="true">&times;</span></button>
                    <i class="fa fa-info-circle"></i> <strong>{{$Mensaje}}</strong>
                    @if($errors->any())
                    <ul class="mb-0 mt-5 small" style="padding-left: 15px;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endif

                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-premium alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="top: -2px;"><span aria-hidden="true">&times;</span></button>
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
                @endif
            </div>

            <div class="vta-card">
                <div class="vta-header">
                    <h1 class="vta-title">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i> Ventas: {{Auth::user()->nombres}} {{Auth::user()->apellidos}}
                    </h1>
                </div>

                <div class="row">
                    <!-- Columna Izquierda: Acciones y Código -->
                    <div class="col-md-5">
                        <div class="mb-20">
                            @if(isset($totalTmp))
                            @if(session('folio_caja'))
                            <!-- Si es una venta de Caja procesada por nivel >= 10 -->
                            <div class="alert alert-info" style="border-radius: 12px; margin-bottom: 10px; padding: 10px;">
                                <i class="fa fa-info-circle"></i> Procesando Folio: <strong>{{ session('folio_caja') }}</strong>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <a class="btn btn-info btn-rounded btn-lg btn-block mb-10" href="javascript:fncFormapago(1)">
                                        <i class="fa fa-credit-card"></i> Tarjeta
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a class="btn btn-success btn-rounded btn-lg btn-block mb-10" href="javascript:fncFormapago(2)">
                                        <i class="fa fa-money"></i> Efectivo
                                    </a>
                                </div>
                            </div>
                            <a class="btn btn-warning btn-rounded btn-lg btn-block mb-10" href="javascript:fncFormapago(3)">
                                <i class="fa fa-university"></i> Transferencia
                            </a>
                            <a class="btn btn-default btn-rounded btn-block" href="{{ route('venta.index', ['clear_folio' => 1]) }}">
                                <i class="fa fa-times"></i> Cancelar/Volver
                            </a>
                            @elseif($userNivel < 10 && isset($cajaPagadora) && $cajaPagadora==1)
                                <!-- Vendedor Nivel 1 con Caja Pagadora habilitada -->
                                <button type="button" class="btn btn-success btn-rounded btn-lg btn-block" onclick="fncFinalizarVenta();">
                                    <i class="fa fa-check"></i> Finalizar
                                </button>
                                @else
                                <!-- Usuario Normal de Caja o Nivel 1 sin Caja Pagadora -->
                                @if($TotaFinal < 0)
                                    <a class="btn btn-success btn-rounded btn-lg btn-block mb-10" href="javascript:fncFormapago(2)">
                                    <i class="fa fa-money"></i> Efectivo
                                    </a>
                                    @else
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <a class="btn btn-info btn-rounded btn-lg btn-block mb-10" href="javascript:fncFormapago(1)">
                                                <i class="fa fa-credit-card"></i> Tarjeta
                                            </a>
                                        </div>
                                        <div class="col-sm-6">
                                            <a class="btn btn-success btn-rounded btn-lg btn-block mb-10" href="javascript:fncFormapago(2)">
                                                <i class="fa fa-money"></i> Efectivo
                                            </a>
                                        </div>
                                    </div>
                                    <a class="btn btn-warning btn-rounded btn-lg btn-block" href="javascript:fncFormapago(3)">
                                        <i class="fa fa-university"></i> Transferencia
                                    </a>
                                    @endif
                                    @endif
                                    @endif
                        </div>

                        <div class="codigo-container">
                            <label class="codigo-label">Escanear Producto</label>
                            <form class="form-horizontal" name="form1" id="form1" method="POST" action="{{ route('venta.store') }}">
                                @csrf
                                <input type="text" name="codigo" id="codigo" onkeypress="fncBuscaProducto(event)" autofocus autocomplete="off">

                                <input type="hidden" name="accion" id="accion">
                                <input type="hidden" name="formaPago" id="formaPago" value="">
                                <input type="hidden" name="ventaIdProducto" id="ventaIdProducto" value="">
                                <input type="hidden" name="ventaNombreProducto" id="ventaNombreProducto" value="">
                                <input type="hidden" name="ventaValorProducto" id="ventaValorProducto" value="">
                                <input type="hidden" name="codBarraEfectivo" id="codBarraEfectivo" value="{{ $codBarraEfectivo }}">
                                <input type="hidden" name="codBarraTarjeta" id="codBarraTarjeta" value="{{ $codBarraTarjeta }}">
                                <input type="hidden" name="codigoBorrar" id="codigoBorrar" value="">
                                @if(isset($totalTmp))
                                <input type="hidden" name="totalPagar" id="totalPagar" value="{{$totalTmp}}">
                                @endif
                                <input type="hidden" name="montoSencillo" id="montoSencillo" value="">
                                <input type="hidden" name="totalVuelto" id="totalVuelto" value="">
                            </form>
                        </div>

                        @if(isset($consultaPrecio) && $consultaPrecio == 1)
                        <button type="button" class="btn btn-primary btn-lg btn-rounded btn-block" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);" onclick="fncAbrirConsultaPrecio()">
                            <i class="fa fa-search"></i> CONSULTA PRECIO
                        </button>
                        <p class="text-center small text-muted mt-10">Prueba hasta el 10 Marzo</p>
                        @endif

                        @if(isset($totalTmp))
                        <div class="total-box">
                            <div class="total-row">
                                <span class="total-label">Subtotal Venta:</span>
                                <span class="total-value">${{ $totalTmp }}</span>
                            </div>
                            @if (isset($totalDevolucion))
                            <div class="total-row">
                                <span class="total-label">Devolución:</span>
                                <span class="total-value devolucion-value">-${{ $totalDevolucion }}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">
                                    @if($TotaFinal < 0) Devolver al cliente @else Cliente paga @endif:
                                        </span>
                                        <span class="total-value">${{ str_replace('-','',$TotaFinal) }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Columna Derecha: Detalle de Venta -->
                    <div class="col-md-7">
                        @if(isset($ventaTmp) and count($ventaTmp) > 0)
                        <div class="table-responsive">
                            <table class="table table-vta">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">Unit</th>
                                        <th class="text-right">Sub</th>
                                        <th class="text-right"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventaTmp as $vtaTmp)
                                    <tr>
                                        <td>
                                            <div style="font-size: 20px; font-weight: 700; color: #2c3e50;">{{$vtaTmp->descripcion}}</div>
                                            <small class="text-muted" style="font-size: 14px;">{{$vtaTmp->codigo}}</small>
                                        </td>
                                        <td class="text-center">
                                            <div style="display: flex; align-items: center; justify-content: center;">
                                                <a class="btn btn-danger btn-xs" href="javascript:fncBorrarProducto('{{$vtaTmp->codigo}}')" style="border-radius: 4px; padding: 2px 8px;">-</a>
                                                <span style="font-size: 18px; font-weight: 700; margin: 0 10px;">{{$vtaTmp->cantidad}}</span>
                                                @if(stripos($vtaTmp->codigo,"21000") === false)
                                                <a class="btn btn-success btn-xs" href="javascript:fncSumarProducto('{{$vtaTmp->codigo}}')" style="border-radius: 4px; padding: 2px 8px;">+</a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-right">${{ $vtaTmp->precio_venta_ori }}</td>
                                        <td class="text-right price-tag">${{ $vtaTmp->precio_venta }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('venta.destroy',$vtaTmp->codigo) }}" class="text-danger" title="Eliminar">
                                                <i class="fa fa-trash-o fa-lg"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center" style="padding: 60px 20px; color: #94a3b8;">
                            <i class="fa fa-shopping-basket fa-4x mb-20" aria-hidden="true" style="opacity: 0.3;"></i>
                            <h4>No hay productos en esta venta</h4>
                            <p>Escanee un producto o use la búsqueda para comenzar.</p>
                        </div>
                        @endif

                        <!-- Listado de Búsqueda -->
                        @if(isset($listadoProductos) and count($listadoProductos) > 0)
                        <div class="mt-20">
                            <h5 style="font-weight: 700; color: #64748b; margin-bottom: 15px; text-transform: uppercase; font-size: 13px;">Resultados de Búsqueda</h5>
                            <div class="table-responsive">
                                <table class="table table-vta" style="background: #f8fafc; border-radius: 10px;">
                                    <tbody>
                                        @foreach($listadoProductos as $lsProd)
                                        <tr id="cod{{$lsProd->codigo}}">
                                            <td>
                                                <div style="font-weight: 600;">{{$lsProd->descripcion}}</div>
                                                <small class="text-muted">{{$lsProd->codigo}}</small>
                                            </td>
                                            <td class="text-right price-tag">${{$lsProd->precio_venta}}</td>
                                            <td class="text-right">
                                                <button class="btn btn-success btn-sm btn-rounded" type="button" onclick="fncAceptaProducto('{{$lsProd->codigo}}');">
                                                    <i class="fa fa-plus"></i> Añadir
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
<script language="javascript">
    const codigoInput = document.getElementById('codigo');
    if (codigoInput) {
        codigoInput.focus();
    }

    function fncAceptaProducto(codigo) {
        document.getElementById("codigo").value = codigo;
        document.getElementById("form1").action = "{{ route('venta.store') }}";
        document.getElementById("form1").submit();
    }

    function fncAbrirConsultaPrecio() {
        window.open("{{ route('ConsultaPrecio.index') }}?minimal=1", "ConsultaPrecio", "width=900,height=700,scrollbars=yes,resizable=yes");
    }

    function fncFinalizarVenta() {
        if (confirm('¿Desea enviar esta venta a Caja?')) {
            document.getElementById("form1").action = "{{ route('venta.park') }}";
            document.getElementById("form1").submit();
        }
    }

    function fncFormapago(forma) {
        //Te lleva a la strore del PagoController
        document.getElementById('formaPago').value = forma;
        document.getElementById("form1").action = "{{ route('pago.index')}}";
        document.getElementById("form1").submit();
    }

    function fncSumarProducto(producto) {
        document.getElementById("codigo").value = producto;
        document.getElementById("accion").value = "addProducto";
        document.getElementById("form1").action = "{{ route('venta.store') }}";
        document.getElementById("form1").submit();
    }

    function fncBorrarProducto(producto) {
        document.getElementById("codigo").value = producto;
        document.getElementById("accion").value = "delProducto";
        document.getElementById("form1").action = "{{ route('venta.store') }}";
        document.getElementById("form1").submit();
    }

    function fncBuscaProducto(e) {
        var valoresOK = false;
        var codEfectivo = document.form1.codBarraEfectivo.value.trim();
        var codTarjeta = document.form1.codBarraTarjeta.value.trim();
        var inputCodigo = document.form1.codigo.value.substr(0, 7);

        if (inputCodigo === codEfectivo || inputCodigo === codTarjeta) {
            // Caso Caja Pagadora habilitada y nivel bajo (vendedor)
            if (window.AppConfig.cajaPagadora == 1 && window.AppConfig.userNivel < 10) {
                if (document.getElementById("totalPagar") && document.getElementById("totalPagar").value.length > 0 && document.getElementById("totalPagar").value != "0") {
                    document.getElementById("form1").action = "{{ route('venta.store') }}";
                    document.getElementById("form1").submit();
                    return true;
                }
                return false;
            }

            if (!document.getElementById("totalPagar") || document.getElementById("totalPagar").value.length === 0) {
                alert("No se puede ejecutar la acción si no hay un producto seleccionado.");
                document.form1.codigo.value = "";
                return false;
            } else {
                if (inputCodigo === codTarjeta) {
                    document.getElementById("formaPago").value = "1";
                    document.getElementById("totalVuelto").value = null;
                    document.getElementById("form1").action = "{{ route('pago.update',1) }}";
                    let methodInput = document.getElementById('_method');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.id = '_method';
                        methodInput.value = 'PUT';
                        document.getElementById("form1").appendChild(methodInput);
                    } else {
                        methodInput.value = 'PUT';
                    }
                    document.getElementById("form1").submit();
                    return true;
                }
                if (inputCodigo === codEfectivo) {
                    document.getElementById("formaPago").value = "2";
                    document.getElementById("montoSencillo").value = document.getElementById("totalPagar").value.replace('.', '');
                    document.getElementById("totalPagar").value = document.getElementById("totalPagar").value.replace('.', '');
                    document.getElementById("totalVuelto").value = 0;
                    document.getElementById("form1").action = "{{ route('pago.update',2) }}";
                    let methodInput = document.getElementById('_method');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.id = '_method';
                        methodInput.value = 'PUT';
                        document.getElementById("form1").appendChild(methodInput);
                    } else {
                        methodInput.value = 'PUT';
                    }
                    document.getElementById("form1").submit();
                    return true;
                }
            }
        }
        if (e.keyCode === 13) {
            document.getElementById("form1").action = "{{ route('venta.store') }}";
            document.getElementById("form1").submit();
        }
    }


    function fncBuscaProducto1(e) {
        var valoresOK = false;

        if (e.keyCode === 42) {
            alert("Tarjeta Debito");
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