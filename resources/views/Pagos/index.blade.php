@extends('layouts.app')
@section('content')
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Lato', sans-serif;
    }

    .pago-container {
        padding: 30px 15px;
    }

    .pago-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .pago-header {
        border-bottom: 2px solid #edeff2;
        padding-bottom: 15px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pago-title {
        font-size: 24px;
        font-weight: 800;
        color: #2c3e50;
        margin: 0;
    }

    /* Billetes y Monedas */
    .cash-option {
        border: none;
        background: none;
        padding: 5px;
        transition: transform 0.2s ease;
        outline: none !important;
    }

    .cash-option:hover {
        transform: scale(1.05);
    }

    .cash-option img {
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 120px;
    }

    /* Totales */
    .summary-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 25px;
        border: 1px solid #e2e8f0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .summary-row.total {
        padding-top: 15px;
        border-top: 2px solid #cbd5e0;
        margin-top: 10px;
    }

    .summary-label {
        font-size: 16px;
        font-weight: 600;
        color: #64748b;
    }

    .summary-value {
        font-size: 22px;
        font-weight: 800;
        color: #2c3e50;
    }

    .summary-value.vuelto {
        color: #27ae60;
        font-size: 28px;
    }

    /* Tablas Detalle */
    .table-pago {
        margin-top: 20px;
    }

    .table-pago thead th {
        background: #f1f5f9;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        color: #475569;
        border: none !important;
        padding: 12px !important;
    }

    .table-pago tbody td {
        padding: 12px !important;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .monto-input {
        font-size: 32px;
        font-weight: 800;
        text-align: center;
        border: 2px solid #3498db;
        border-radius: 10px;
        height: 60px;
        width: 100%;
        color: #2c3e50;
    }

    .mb-20 {
        margin-bottom: 20px;
    }

    .mt-20 {
        margin-top: 20px;
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

<div class="container-fluid pago-container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <!-- Contenedor de Mensajes Fijo -->
            <div class="fixed-alert-container">
                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-premium alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="top: -2px;"><span aria-hidden="true">&times;</span></button>
                    <i class="fa fa-check-circle"></i> {{ $message }}
                </div>
                @endif
            </div>

            <div class="pago-card">
                <div class="pago-header">
                    <h1 class="pago-title">
                        <i class="fa fa-credit-card-alt"></i> Confirmar Pago
                        <small style="font-size: 14px; font-weight: 400; color: #64748b; display: block;">
                            Forma de Pago:
                            @if($formaPago == '1') Tarjeta @elseif($formaPago == '2') Efectivo @else Transferencia @endif
                        </small>
                    </h1>
                </div>

                <form class="form-horizontal" action="{{ route('pago.update', 1) }}" method="POST" name="form1" onsubmit="preventDoubleSubmit(this)">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="formaPago" id="formaPago" value="{{$formaPago}}">
                    <input type="hidden" name="codigoBorrar" id="codigoBorrar" value="">
                    <input type="hidden" name="totalPagar" id="totalPagar" value="{{$totalTmp}}">
                    <input type="hidden" name="totalVuelto" id="totalVuelto" value="">

                    <div class="row">
                        <!-- Columna Izquierda: Acciones y Selección de Efectivo -->
                        <div class="col-md-6">

                            <div class="mb-20">
                                @if($formaPago == '1' || $formaPago == '3')
                                <div class="row">
                                    <div class="col-xs-6">
                                        <button type="button" id="butonAtras1" class="btn btn-warning btn-rounded btn-lg btn-block" onclick="history.back()">
                                            <i class="fa fa-arrow-left"></i> Atrás
                                        </button>
                                    </div>
                                    <div class="col-xs-6">
                                        <button type="submit" id="btn1" class="btn btn-success btn-rounded btn-lg btn-block">
                                            Pagar <i class="fa fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                                @endif

                                @if($formaPago == '2')
                                <div class="row mb-20">
                                    <div class="col-xs-6">
                                        <button type="button" id="butonAtras2" class="btn btn-warning btn-rounded btn-lg btn-block" onclick="history.back()">
                                            <i class="fa fa-arrow-left"></i> Atrás
                                        </button>
                                    </div>
                                    <div class="col-xs-6">
                                        <button type="submit" id="btn2" disabled class="btn btn-success btn-rounded btn-lg btn-block">
                                            Pagar <i class="fa fa-check"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="text-center mb-20">
                                    <label style="font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;">Seleccionar Billetes</label>
                                    <div class="row">
                                        <div class="col-xs-4">
                                            <button type="button" class="cash-option" onclick="fncAumentar(1000)">
                                                <img src="{{ asset('images/billete1000.jpg') }}" alt="1000">
                                            </button>
                                        </div>
                                        <div class="col-xs-4">
                                            <button type="button" class="cash-option" onclick="fncAumentar(2000)">
                                                <img src="{{ asset('images/billete2000.jpg') }}" alt="2000">
                                            </button>
                                        </div>
                                        <div class="col-xs-4">
                                            <button type="button" class="cash-option" onclick="fncAumentar(5000)">
                                                <img src="{{ asset('images/billete5000.jpg') }}" alt="5000">
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row mt-10">
                                        <div class="col-xs-4">
                                            <button type="button" class="cash-option" onclick="fncAumentar(10000)">
                                                <img src="{{ asset('images/billete10000.jpg') }}" alt="10000">
                                            </button>
                                        </div>
                                        <div class="col-xs-4">
                                            <button type="button" class="cash-option" onclick="fncAumentar(20000)">
                                                <img src="{{ asset('images/billete20000.jpg') }}" alt="20000">
                                            </button>
                                        </div>
                                        <div class="col-xs-4">
                                            <button type="button" class="cash-option" onclick="fncAumentar(0)">
                                                <div style="background: #e2e8f0; border-radius: 8px; height: 60px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                                    LIMPIAR
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-20">
                                        <label style="font-weight: 700; color: #64748b; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;">Monedas</label><br>
                                        <button type="button" class="btn btn-info btn-rounded" onclick="fncAumentar(500)">500</button>
                                        <button type="button" class="btn btn-success btn-rounded" onclick="fncAumentar(100)">100</button>
                                        <button type="button" class="btn btn-warning btn-rounded" onclick="fncAumentar(50)">50</button>
                                        <button type="button" class="btn btn-info btn-rounded" onclick="fncAumentar(10)">10</button>
                                    </div>
                                </div>

                                <div class="mb-20">
                                    <label class="summary-label text-center btn-block">Monto Recibido</label>
                                    <input type="number" name="montoSencillo" id="montoSencillo" value="0" step="10" class="monto-input" onkeypress="fncCalulaVuelto()" onchange="fncCalulaVuelto()">
                                </div>
                                @endif
                            </div>

                        </div>

                        <!-- Columna Derecha: Detalle y Resumen -->
                        <div class="col-md-6">

                            <div class="summary-box">
                                <div class="summary-row">
                                    <span class="summary-label">
                                        @if(isset($mensajeFinal) && strlen($mensajeFinal) > 0)
                                        {{$mensajeFinal}}
                                        @else
                                        Total Venta
                                        @endif
                                    </span>
                                    <span class="summary-value" id="IdTotalPagar">${{str_replace('-','',$totalTmp)}}</span>
                                </div>

                                @if (isset($totalDevolucion))
                                <div class="summary-row">
                                    <span class="summary-label">Devolución</span>
                                    <span class="summary-value" style="color: #e74c3c;">-${{$totalDevolucion}}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">
                                        @if($TotaFinal < 0) Devolver al cliente @else Cliente paga @endif
                                            </span>
                                            <span class="summary-value">${{str_replace('-','',$totalTmp)}}</span>
                                </div>
                                @endif

                                <div class="summary-row total">
                                    <span class="summary-label">Vuelto a entregar</span>
                                    <span class="summary-value vuelto" id="IdVuelto">$0</span>
                                </div>
                            </div>

                            @if(isset($ventasTmp) and count($ventasTmp) > 0)
                            <div class="table-responsive">
                                <table class="table table-pago">
                                    <thead>
                                        <tr>
                                            <th>Descripción</th>
                                            <th class="text-center">Cant.</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ventasTmp as $vtaTmp)
                                        <tr>
                                            <td>
                                                <div style="font-weight: 600; font-size: 13px;">{{trim($vtaTmp->descripcion)}}</div>
                                                <small class="text-muted">Unit: ${{trim($vtaTmp->precio_venta)}}</small>
                                            </td>
                                            <td class="text-center">{{trim($vtaTmp->cantidad)}}</td>
                                            <td class="text-right" style="font-weight: 700;">${{trim($vtaTmp->ValorTotal)}}</td>
                                        </tr>
                                        @endforeach

                                        @if(isset($listaDevolucion) and count($listaDevolucion) > 0)
                                        @foreach($listaDevolucion as $lsDev)
                                        <tr class="danger">
                                            <td>
                                                <div style="color: #e74c3c; font-weight: 600;">(DEV) {{trim($lsDev->descripcion)}}</div>
                                            </td>
                                            <td class="text-center">{{trim($lsDev->cantidad)}}</td>
                                            <td class="text-right" style="color: #e74c3c; font-weight: 700;">-${{trim($lsDev->sub_total)}}</td>
                                        </tr>
                                        @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
<script language="javascript">
    // Safe check if element exists before setting property
    const btnPagoMaster = document.getElementById("btn2");

    function fncAumentar(monto) {
        let montoSencilloInput = document.getElementById('montoSencillo');
        if (monto == 0) {
            document.getElementById("btn2").disabled = true;
            montoSencilloInput.value = 0;
            updateVueltoDisplay(0);
        } else {
            let currentMonto = parseInt(montoSencilloInput.value) || 0;
            montoSencilloInput.value = currentMonto + monto;
            fncCalulaVuelto();
        }
    }

    function updateVueltoDisplay(valor) {
        document.getElementById('totalVuelto').value = valor;
        document.getElementById('IdVuelto').innerHTML = "<h3>" + Intl.NumberFormat('es-CL', {
            currency: 'CLP',
            style: 'currency'
        }).format(valor) + "</h3>";
    }

    function fncCalulaVuelto() {
        let totalPagarStr = document.getElementById('totalPagar').value.replace(/[^0-9]/g, '');
        let totalPagar = parseInt(totalPagarStr) || 0;
        let montoSencillo = parseInt(document.getElementById('montoSencillo').value) || 0;

        let valor = (montoSencillo - totalPagar);
        updateVueltoDisplay(valor);

        const btnSubmit = document.getElementById("btn2");
        if (btnSubmit) {
            btnSubmit.disabled = (valor < 0);
        }
    }

    function fncRealizarPago1() {
        let totalPagarStr = document.getElementById('totalPagar').value.replace(/[^0-9]/g, '');
        let totalPagar = parseInt(totalPagarStr) || 0;
        let montoSencillo = parseInt(document.getElementById('montoSencillo').value) || 0;

        if ((montoSencillo - totalPagar) < 0) {
            alert("Revise el Monto ingresado");
            document.getElementById('montoSencillo').focus();
        }
    }

    // Auto-focus logic or initial state
    window.onload = function() {
        if (document.getElementById('montoSencillo')) {
            document.getElementById('montoSencillo').focus();
        }
    };

    function preventDoubleSubmit(form) {
        const btn1 = document.getElementById('btn1');
        const btn2 = document.getElementById('btn2');
        if (btn1) btn1.disabled = true;
        if (btn2) btn2.disabled = true;

        // Change text to show processing
        if (btn1) btn1.innerHTML = 'Procesando... <i class="fa fa-spinner fa-spin"></i>';
        if (btn2) btn2.innerHTML = 'Procesando... <i class="fa fa-spinner fa-spin"></i>';
    }
</script>