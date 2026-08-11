@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card-premium">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <h1 style="font-weight: 900; color: var(--dark); margin: 0;">Panel de Control</h1>
                        <p class="text-muted">Bienvenido de nuevo, {{ Auth::user()->nombres }}.</p>
                    </div>
                    <div class="status-badge" style="background: rgba(39, 174, 96, 0.1); color: var(--success);">
                        SISTEMA ACTIVO
                    </div>
                </div>

                @if (session('status'))
                <div class="alert alert-success alert-premium">
                    {{ session('status') }}
                </div>
                @endif

                @if (!empty($mensaje))
                <div class="alert alert-warning alert-premium">
                    <i class="fa fa-exclamation-triangle"></i> {{ $mensaje }}
                </div>
                @endif

                <div class="row text-center">
                    <div class="col-md-4">
                        <a href="{{ url('/venta') }}" style="text-decoration: none;">
                            <div class="card-premium" style="transition: transform 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i class="fa fa-shopping-cart fa-4x" style="color: var(--primary); margin-bottom: 20px;"></i>
                                <h3 style="font-weight: 800; color: var(--dark);">Ventas</h3>
                                <p class="text-muted">Realizar nuevas ventas y cobros.</p>
                                <span class="btn btn-primary btn-rounded btn-sm">Acceder</span>
                            </div>
                        </a>
                    </div>

                    @if(Auth::user()->nivel >= 10)
                    <div class="col-md-4">
                        <a href="{{ url('/stock') }}" style="text-decoration: none;">
                            <div class="card-premium" style="transition: transform 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i class="fa fa-archive fa-4x" style="color: var(--warning); margin-bottom: 20px;"></i>
                                <h3 style="font-weight: 800; color: var(--dark);">Productos</h3>
                                <p class="text-muted">Gestionar inventario y precios.</p>
                                <span class="btn btn-warning btn-rounded btn-sm">Gestionar</span>
                            </div>
                        </a>
                    </div>
                    @endif

                    <div class="col-md-4">
                        <a href="{{ url('/ResumenVenta') }}" style="text-decoration: none;">
                            <div class="card-premium" style="transition: transform 0.3s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i class="fa fa-line-chart fa-4x" style="color: var(--success); margin-bottom: 20px;"></i>
                                <h3 style="font-weight: 800; color: var(--dark);">Reportes</h3>
                                <p class="text-muted">Ver resumen de ventas y cierres.</p>
                                <span class="btn btn-success btn-rounded btn-sm">Ver Reportes</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection