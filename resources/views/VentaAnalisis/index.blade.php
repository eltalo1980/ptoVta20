@extends('layouts.app')
@section('content')

<div class="container my-5">
    @php
    $hoy = date('Y-m-d');
    @endphp

    <form method="GET" action="{{ route('VentaAnalisis.index') }}">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio', $hoy) }}">
            </div>
            <div class="col-md-4">
                <label for="fecha_final" class="form-label">Fecha Final</label>
                <input type="date" id="fecha_final" name="fecha_final" class="form-control" value="{{ request('fecha_final', $hoy) }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100" id="btnCalcular">Calcular</button>
            </div>
        </div>
    </form>

    <div class="my-4">
        <h5>Ventas por Hora</h5>
        <canvas id="ventasPorHoraBar" height="80"></canvas>
    </div>

    <div class="my-4">
        <h5>Ranking de Productos</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Total Venta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rankingProductos as $item)
                <tr>
                    <td>{{ $item->descripcion_limpia ?? $item->descripcion }}</td>
                    <td>{{ $item->cantidad }}</td>
                    <td>${{ number_format((float)($item->total_venta ?? 0), 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="my-4">
        <canvas id="rankingProductosPie"></canvas>
    </div>

    @php
    $labels = [];
    $data = [];
    $total_venta = [];
    $labels_datalabels = [];
    if (!empty($rankingProductos) && is_iterable($rankingProductos)) {
    foreach($rankingProductos as $item) {
    $labels[] = $item->descripcion_limpia ?? $item->descripcion;
    $data[] = $item->cantidad;
    $total_venta[] = $item->total_venta;
    $labels_datalabels[] = ($item->descripcion_limpia ?? $item->descripcion) . "\n" . $item->cantidad . " ventas\n$" . number_format((float)($item->total_venta ?? 0), 0, ',', '.');
    }
    }

    $barLabels = [];
    $barData = [];
    $horaData = [];
    if (!empty($horaMayorVentas) && is_iterable($horaMayorVentas)) {
    foreach($horaMayorVentas as $item) {
    $hora = str_pad($item->hora, 2, '0', STR_PAD_LEFT);
    $horaData[$hora] = $item->total;
    }
    ksort($horaData, SORT_NUMERIC);
    foreach($horaData as $hora => $total) {
    $barLabels[] = $hora . ':00';
    $barData[] = $total;
    }
    }
    @endphp


</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('rankingProductosPie').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {
                    !!json_encode($labels) !!
                },
                datasets: [{
                    data: {
                        !!json_encode($data) !!
                    },
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#C9CBCF', '#FF6384',
                        '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#8BC34A', '#E91E63', '#00BCD4', '#CDDC39',
                        '#FFC107', '#795548', '#607D8B', '#F44336',
                        '#3F51B5', '#009688', '#FFEB3B', '#673AB7',
                        '#FF5722', '#BDBDBD', '#2196F3', '#AED581',
                        '#D4E157', '#FFB300', '#8D6E63', '#B39DDB'
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Ranking de Productos'
                    },
                    datalabels: {
                        color: '#fff',
                        formatter: function(value, context) {
                            const labels = {
                                !!json_encode($labels_datalabels) !!
                            };
                            return labels[context.dataIndex];
                        },
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        align: 'center',
                        anchor: 'center'
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Bar chart for ventas por hora
        const ctxBar = document.getElementById('ventasPorHoraBar').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: {
                    !!json_encode($barLabels) !!
                },
                datasets: [{
                    label: 'Total Vendido',
                    data: {
                        !!json_encode($barData) !!
                    },
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hora'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Vendido'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Ventas por Hora'
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>