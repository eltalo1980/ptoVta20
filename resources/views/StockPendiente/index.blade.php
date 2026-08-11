@extends('layouts.app')
@section('content')

<div class="container my-5">
    <section class="">
        <section id="demo" class="">
            <strong>Productos Pendientes</strong>
            <div class="row">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span id="card_title"></span>
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

                <div class="col-md-12 mb-4">

                    <!-- Formulario de filtros -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form class="form-horizontal" name="form1" id="form1" method="GET" action="{{route('StockPendiente.index')}}">
                                @csrf
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label">Empresa</label>
                                        <select name="cmbEmpresa" id="cmbEmpresa" class="form-select" onchange="fncCambiaEmpresa()">
                                            <option value="">Todas las empresas</option>
                                            @if(isset($empresasPendientes))
                                            @foreach($empresasPendientes as $lsEmp)
                                            <option value="{{$lsEmp->empresa}}"
                                                {{(isset($empresaSeleccionada) && $empresaSeleccionada == $lsEmp->empresa) ? 'selected' : ''}}>
                                                {{$lsEmp->empresa}}
                                            </option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- productos seleccionados-->
                    @if(isset($listPendiente) and count($listPendiente)>0 )
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Empresa</th>
                                    <th scope="col">Cantidad</th>
                                    <th scope="col">Precio Costo</th>
                                    <th scope="col">Precio Venta</th>
                                    <th scope="col">Precio Costo TOT</th>
                                    <th scope="col">Precio Venta TOT</th>
                                    <th scope="col">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPendiente as $lsPend)
                                <tr>
                                    <td><small>{{ $lsPend->codigo }}</small></td>
                                    <td><strong>{{ $lsPend->descripcion }}</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $lsPend->empresa }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $lsPend->cantidad }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format((float)($lsPend->precio_costo ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end"><strong>{{ number_format((float)($lsPend->precio_venta ?? 0), 0, ',', '.') }}</strong></td>
                                    <td class="text-end">{{ number_format((float)(($lsPend->cantidad ?? 0) * ($lsPend->precio_costo ?? 0)), 0, ',', '.') }}</td>
                                    <td class="text-end"><strong>{{ number_format((float)(($lsPend->cantidad ?? 0) * ($lsPend->precio_venta ?? 0)), 0, ',', '.') }}</strong></td>
                                    <td>
                                        <form action="{{ route('StockPendiente.destroy',$lsPend->id_producto) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro?')">
                                                <i class="glyphicon glyphicon-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-info">
                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                    <td class="text-center"><strong>{{ $listPendiente->sum('cantidad') }}</strong></td>
                                    <td class="text-end"><strong>{{ number_format((float)($listPendiente->sum('precio_costo') ?? 0), 0, ',', '.') }}</strong></td>
                                    <td class="text-end"><strong>{{ number_format((float)($listPendiente->sum('precio_venta') ?? 0), 0, ',', '.') }}</strong></td>
                                    <td class="text-end">
                                        <strong>
                                            {{ number_format((float)($listPendiente->sum(function($item) { 
                                            return ($item->cantidad ?? 0) * ($item->precio_costo ?? 0); 
                                        }) ?? 0), 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>
                                            {{ number_format((float)($listPendiente->sum(function($item) { 
                                            return ($item->cantidad ?? 0) * ($item->precio_venta ?? 0); 
                                        }) ?? 0), 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form action="{{ route('StockPendiente.destroyAll') }}" method="POST" class="mt-3">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id_producto" id="id_producto" value="All">
                        <input type="hidden" name="empresa_eliminar" id="empresa_eliminar" value="{{ isset($empresaSeleccionada) ? $empresaSeleccionada : '' }}">
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar TODOS los productos pendientes?')">
                            <i class="glyphicon glyphicon-trash"></i> Eliminar Todos
                        </button>
                    </form>

                    @else
                    <div class="alert alert-info text-center">
                        <i class="glyphicon glyphicon-search fa-3x mb-3"></i>
                        <h5>No se encontraron productos pendientes</h5>
                        @if(isset($codigo) || isset($empresaSeleccionada))
                        <p class="mb-0">Intente con otros criterios de búsqueda</p>
                        @else
                        <p class="mb-0">No hay productos en el listado pendiente</p>
                        @endif
                    </div>
                    @endif
                </div>
        </section>
    </section>
</div>

<script language="javascript">
    function fncBuscaProducto(e) {
        if (e.keyCode === 13) {
            document.getElementById("form1").submit();
        }
    }

    function fncCambiaEmpresa() {
        var empresa = document.getElementById("cmbEmpresa").value;
        document.getElementById("empresa_eliminar").value = empresa;
        document.getElementById("form1").submit();
    }
</script>

@endsection