@extends('layouts.app')
@section('content')
<style>
    body {
        background-color: #f0f2f5;
        font-family: 'Lato', sans-serif;
    }

    .caja-container {
        padding: 30px 15px;
    }

    .caja-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .caja-header {
        border-bottom: 2px solid #edeff2;
        padding-bottom: 15px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .caja-title {
        font-size: 24px;
        font-weight: 800;
        color: #2c3e50;
        margin: 0;
    }

    .table-caja thead th {
        background-color: #f8fafc;
        color: #64748b;
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 12px !important;
    }

    .table-caja tbody td {
        padding: 15px 12px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }

    .price-tag {
        font-weight: 800;
        color: #27ae60;
        font-size: 18px;
    }

    .folio-badge {
        background: #e2e8f0;
        color: #475569;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
    }

    .vendedor-tag {
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<div class="container-fluid caja-container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="caja-card">
                <div class="caja-header">
                    <a href="{{ route('Caja.index') }}" class="btn btn-info btn-rounded">
                        <i class="fa fa-refresh"></i> Actualizar
                    </a>
                    <h1 class="caja-title">
                        <i class="fa fa-university"></i> Panel de Caja: Ventas Pendientes
                    </h1>
                    <a href="{{ route('Caja.index') }}" class="btn btn-info btn-rounded">
                        <i class="fa fa-refresh"></i> Actualizar
                    </a>
                </div>

                @if(count($ventasPendientes) > 0)
                <div class="table-responsive">
                    <table class="table table-caja">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Folio</th>
                                <th>Vendedor</th>
                                <th class="text-center">Pago</th>
                                <th class="text-center">Productos</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventasPendientes as $venta)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</td>
                                <td><span class="folio-badge">{{ $venta->folio_caja }}</span></td>
                                <td>
                                    <div class="vendedor-tag">
                                        {{ $usuarios[$venta->id_usuario]->nombres ?? 'Desconocido' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="label {{ $venta->metodo_pago == 'EFECTIVO' ? 'label-success' : ($venta->metodo_pago == 'TARJETA' ? 'label-info' : 'label-default') }}">
                                        {{ $venta->metodo_pago }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $venta->items }}</td>
                                <td class="text-right price-tag">${{ number_format((float)($venta->total ?? 0), 0, ',', '.') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('Caja.show', $venta->folio_caja) }}" class="btn btn-primary btn-sm btn-rounded">
                                        <i class="fa fa-shopping-cart"></i> Cobrar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center" style="padding: 60px 20px; color: #94a3b8;">
                    <i class="fa fa-clock-o fa-4x mb-20" aria-hidden="true" style="opacity: 0.3;"></i>
                    <h4>No hay ventas pendientes en caja</h4>
                    <p>Las ventas que los vendedores finalicen aparecerán aquí.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection