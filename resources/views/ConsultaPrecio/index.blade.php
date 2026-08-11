@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 10px;">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info" style="border-radius: 12px; overflow: hidden; box-shadow: 0 8px 15px rgba(0,0,0,0.1);">
                <div class="panel-heading" style="background: linear-gradient(135deg, #3498db, #2980b9); color: white; padding: 12px;">
                    <h2 class="panel-title" style="font-size: 18px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa fa-search"></i> Consulta Precios</span>
                        <a href="javascript:void(0)" onclick="fncCerrarVentana()" class="btn btn-danger btn-xs" style="border-radius: 15px; padding: 2px 10px;">Cerrar</a>
                    </h2>
                </div>
                <div class="panel-body" style="background-color: #f8f9fa; padding: 15px;">

                    @if(session('Mensaje'))
                    <div class="{{ session('Estilo') }}" style="border-radius: 8px; margin-bottom: 12px; padding: 8px; font-size: 13px;">
                        <strong>{{ session('Mensaje') }}</strong>
                    </div>
                    @endif

                    <form action="{{ route('ConsultaPrecio.store') }}" method="POST" id="formConsulta" data-timeout="{{ ($tiempoVerConsultaPrecio ?? 1) * 1000 }}">
                        @csrf
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label for="codigo" style="font-size: 14px;">Escanee el Código</label>
                            <input type="text" name="codigo" id="codigo" class="form-control"
                                style="font-size: 22px; height: 45px; border-radius: 8px; border: 2px solid #3498db; text-align: center;"
                                autofocus autocomplete="off">
                            <input type="hidden" name="minimal" value="1">
                        </div>
                    </form>

                    <div style="margin: 10px 0;">
                        <h4 style="margin: 0; font-size: 15px; font-weight: bold;">Resultados</h4>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" style="background: white; border-radius: 8px; overflow: hidden; margin-bottom: 0;">
                            <thead style="background-color: #34495e; color: white;">
                                <tr style="font-size: 12px;">
                                    <th style="padding: 8px;">DESCRIPCIÓN</th>
                                    <th class="text-right" style="padding: 8px;">PRECIO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productosConsulta as $prod)
                                <tr style="font-size: 15px; border-bottom: 1px solid #eee;">
                                    <td style="vertical-align: middle; padding: 8px;">{{ $prod->descripcion }}</td>
                                    <td class="text-right" style="font-size: 20px; font-weight: bold; color: #27ae60; vertical-align: middle; padding: 8px;">
                                        ${{ number_format((float)($prod->precio_venta ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center" style="padding: 20px; font-style: italic; color: #95a5a6; font-size: 13px;">
                                        Sin consultas recientes.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Redimensionar automáticamente la ventana a un tamaño más compacto
    window.resizeTo(500, 500);

    // Mantener el foco en el input siempre
    const focusCodigo = () => {
        const el = document.getElementById('codigo');
        if (el) el.focus();
    };

    focusCodigo();
    document.addEventListener('click', focusCodigo);

    let isInternalNavigation = false;

    // Si hay resultados o un mensaje de éxito, cerrar automáticamente tras el tiempo configurado
    @if(session('Mensaje') && session('Estilo') == 'alert alert-success')
    var vTiempoCierre = document.getElementById('formConsulta').getAttribute('data-timeout') || 1000;
    setTimeout(function() {
        fncCerrarVentana();
    }, vTiempoCierre);
    @endif

    // Envío automático al escanear (Enter)
    document.getElementById('formConsulta').addEventListener('submit', function() {
        isInternalNavigation = true;
    });

    document.getElementById('codigo').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            isInternalNavigation = true;
            document.getElementById('formConsulta').submit();
        }
    });

    /**
     * Limpia los registros de consulta y cierra la ventana
     */
    function fncCerrarVentana() {
        isInternalNavigation = true; // Evitar doble limpieza
        $.post("{{ route('ConsultaPrecio.clear') }}", {
            _token: "{{ csrf_token() }}"
        }, function() {
            window.close();
        });
    }

    /**
     * Asegura la limpieza si el usuario cierra la ventana manualmente (X del navegador)
     * Solo se ejecuta si NO es una navegación interna (submit del form)
     */
    window.addEventListener('beforeunload', function(e) {
        if (!isInternalNavigation) {
            const url = "{{ route('ConsultaPrecio.clear') }}";
            const formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            navigator.sendBeacon(url, formData);
        }
    });
</script>
@endsection