@extends('layouts.app')
@section('content')


<div class="container my-5">
    <!-- Section: Components -->
    <section class="">
        <section id="demo" class="">
            <strong>Informe Ventas</strong>
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
                        <div class="row">
                            <div class="col-md-6">
                                <input type="hidden" name="fechaCierre" id="fechaCierre">
                                <select name="cmBfechaCierre" id="cmBfechaCierre" class="form-control " onchange="fncCambiaFecha()">
                                    @if(isset($listaFechas) and count($listaFechas)>0 )
                                    <option value=""></option>
                                    @foreach($listaFechas as $lsFecha)
                                    <option value="{{$lsFecha->fecha_movimiento}}">{{$lsFecha->fecha_movimiento}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('CierreDia.exportCSV', ['fecha' => $diaConsulta]) }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Exportar CSV
                                </a>
                            </div>
                        </div>
                </div> <!-- si elimino este queda como tabla responsiva -->

                @if(isset($ventasPorMedio) and count($ventasPorMedio)>0 )
                <br>
                <div class="card">
                    <div class="card-header"><b>Ventas Por Medio</b></div>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>

                                <th scope="col">FechaVenta</th>
                                <th scope="col">FormaPago</th>
                                <th scope="col">Neto</th>
                                <th scope="col">IVA</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventasPorMedio as $lsVtaMedio)
                            <tr>
                                <td>{{$lsVtaMedio->fecha_venta}}</td>
                                <td>{{$lsVtaMedio->forma_pago}}</td>
                                <td>{{$lsVtaMedio->neto}}</td>
                                <td>{{$lsVtaMedio->iva}}</td>
                                <td>{{$lsVtaMedio->Total}}</td>
                            </tr>
                            @endforeach
                            <tr class="success">
                                <td></td>
                                <td>Total</td>
                                <td>{{$ventasPorMedioTotal[0]->neto}}</td>
                                <td>{{$ventasPorMedioTotal[0]->iva}}</td>
                                <td>{{$ventasPorMedioTotal[0]->Total}}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>

                @if(isset($ventasBorradas) and count($ventasBorradas)>0 )
                <br>
                <div class="card">
                    <div class="card-header"><b>Ventas Borradas</b></div>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>

                                <th scope="col">FechaVenta</th>
                                <th scope="col">FormaPago</th>
                                <th scope="col">Neto</th>
                                <th scope="col">IVA</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="danger">
                                <td>{{$ventasBorradas[0]->fecha_venta}}</td>
                                <td>Total</td>
                                <td>{{$ventasBorradas[0]->neto}}</td>
                                <td>{{$ventasBorradas[0]->IVA}}</td>
                                <td>{{$ventasBorradas[0]->Total}}</td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>

                @if(isset($ventasPorEmpresa) and count($ventasPorEmpresa)>0 )
                <div class="card">
                    <div class="card-header"><b>Ventas por Empresa</b></div>
                    <table class="table table-striped">
                        <thead>
                            <tr>

                                <th scope="col">FechaVenta</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Neto</th>
                                <th scope="col">IVA</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventasPorEmpresa as $lsVtaDpto)
                            <tr>
                                <td>{{$diaConsulta}}</td>
                                <td>{{$lsVtaDpto->empresa}}</td>
                                <td>{{$lsVtaDpto->neto}}</td>
                                <td>{{$lsVtaDpto->iva}}</td>
                                <td>{{$lsVtaDpto->Total}}</td>
                            </tr>
                            @endforeach
                            <tr class="success">
                                <td></td>
                                <td>Total</td>
                                <td>{{$ventasPorEmpresaTotal[0]->neto}}</td>
                                <td>{{$ventasPorEmpresaTotal[0]->iva}}</td>
                                <td>{{$ventasPorEmpresaTotal[0]->Total}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
                @if(isset($pagosPorEmpresa) and count($pagosPorEmpresa)>0 )
                <div class="card">
                    <div class="card-header"><b>Pagos por Empresa</b></div>
                    <table class="table table-striped">
                        <thead>
                            <tr>

                                <th scope="col">FechaVenta</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagosPorEmpresa as $lsPagoDpto)
                            <tr>
                                <td>{{$diaConsulta}}</td>
                                <td>{{$lsPagoDpto->empresa}}</td>
                                <td>{{$lsPagoDpto->factura_monto}}</td>
                            </tr>
                            @endforeach
                            <tr class="success">
                                <td></td>
                                <td>Total</td>
                                <td>{{$pagosPorEmpresaTotal[0]->factura_monto_total}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
                @if(isset($ventasPorProducto) and count($ventasPorProducto)>0 )
                <div class="card">
                    <div class="card-header"><b>Ventas por Producto Unidad</b></div>

                    <table class="table table-striped">
                        <thead>
                            <tr>

                                <th scope="col">Producto</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">neto</th>
                                <th scope="col">iva</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventasPorProducto as $lsVlStk)
                            <tr>
                                <td>{{$lsVlStk->Producto}}</td>
                                <td>{{$lsVlStk->precio_venta}}</td>
                                <td>{{$lsVlStk->Cantidad}}</td>
                                <td>{{$lsVlStk->neto}}</td>
                                <td>{{$lsVlStk->iva}}</td>
                                <td>{{$lsVlStk->Total}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                @if(isset($ventasPorProductoPeso) and count($ventasPorProductoPeso)>0 )
                <div class="card">
                    <div class="card-header"><b>Ventas por Producto Peso</b></div>

                    <table class="table table-striped">
                        <thead>
                            <tr>

                                <th scope="col">Producto</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">neto</th>
                                <th scope="col">iva</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventasPorProductoPeso as $lsVlStk)
                            <tr>
                                <td>{{$lsVlStk->Producto}}</td>
                                <td>{{$lsVlStk->precio_venta}}</td>
                                <td>{{$lsVlStk->Cantidad}}</td>
                                <td>{{$lsVlStk->neto}}</td>
                                <td>{{$lsVlStk->iva}}</td>
                                <td>{{$lsVlStk->Total}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                </form>

                <!--Section: Shadows-->
        </section>
    </section>
    <!-- Section: Components -->
</div>
@endsection
<script language="javascript">
    function fncCambiaFecha() {
        document.getElementById("fechaCierre").value = document.getElementById("cmBfechaCierre").value;
        document.getElementById("form1").action = "{{ route('CierreDia.index') }}";
        document.getElementById("form1").submit();
    }
</script>