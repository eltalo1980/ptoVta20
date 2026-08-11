<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DondeElMacos | Premium POS</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2575fc;
            --secondary: #6a11cb;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Lato', sans-serif;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        /* Minimal Mode */
        @if(request()->has('minimal') || request('minimal')==1) .main-navbar, .nav-spacer {
            display: none !important;
        }

        body {
            background-color: transparent !important;
        }

        @endif

        /* Navigation */
        .main-navbar {
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 10px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
        }

        .navbar-toggle {
            border: 1px solid var(--border) !important;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .navbar-toggle .icon-bar {
            background-color: var(--primary) !important;
        }

        .navbar-toggle:hover {
            background-color: #f8fafc !important;
        }

        @media (max-width: 767px) {
            .navbar-collapse {
                background: white;
                border-top: 1px solid var(--border);
                margin-top: 10px;
                max-height: 400px;
                overflow-y: auto;
            }

            .nav-link-premium {
                padding: 12px 20px !important;
                border-radius: 0 !important;
                border-bottom: 1px solid #f8fafc;
            }

            .navbar-header {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                padding: 0 15px;
            }

            .navbar-brand-premium {
                float: none !important;
                margin: 0 !important;
                padding-left: 0 !important;
            }

            .navbar-toggle {
                float: none !important;
                margin-right: 0 !important;
            }
        }

        .navbar-brand-premium {
            font-weight: 900;
            font-size: 20px;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .navbar-brand-premium i {
            margin-right: 10px;
            color: var(--primary);
        }

        .nav-link-premium {
            padding: 10px 15px !important;
            margin: 0 2px;
            border-radius: 8px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            color: #64748b !important;
            transition: all 0.2s ease !important;
        }

        .nav-link-premium:hover {
            background: #f1f5f9 !important;
            color: var(--primary) !important;
        }

        .nav-link-premium.active {
            background: rgba(37, 117, 252, 0.1) !important;
            color: var(--primary) !important;
        }

        /* Global Premium Styles */
        .card-premium {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border);
        }

        .btn-rounded {
            border-radius: 50px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            border: none !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-rounded:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
            filter: brightness(1.1);
        }

        .btn-success.btn-rounded {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%) !important;
            color: white !important;
        }

        .btn-info.btn-rounded {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
            color: white !important;
        }

        .btn-primary.btn-rounded {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%) !important;
            color: white !important;
        }

        .btn-warning.btn-rounded {
            background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%) !important;
            color: white !important;
        }

        .btn-danger.btn-rounded {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
            color: white !important;
        }

        .btn-dark.btn-rounded {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important;
            color: white !important;
        }

        /* Tables */
        .table-premium {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .table-premium thead th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            padding: 15px !important;
            border-bottom: 2px solid var(--border) !important;
        }

        .table-premium tbody td {
            padding: 15px !important;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle !important;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .table-premium tr.danger td {
            background-color: #fff1f0 !important;
            transition: all 0.3s ease;
        }

        .table-premium tr.danger:hover td {
            background-color: #fee2e2 !important;
        }

        .table-premium tr.warning td {
            background-color: #fffbe6 !important;
            transition: all 0.3s ease;
        }

        .table-premium tr.warning:hover td {
            background-color: #fff7e6 !important;
        }

        .table-premium tr.success td {
            background-color: #f6ffed !important;
            transition: all 0.3s ease;
        }

        .table-premium tr.success:hover td {
            background-color: #f0fdf4 !important;
        }
    </style>
</head>

<body>
    @php $isMinimal = request()->has('minimal') || request('minimal') == 1; @endphp

    @if (!$isMinimal && !Auth::guest())
    <nav class="navbar main-navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-nav-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand navbar-brand-premium" href="{{ url('/home') }}">
                    <i class="fa fa-shopping-basket"></i> DonElMarco
                </a>
            </div>

            <div class="collapse navbar-collapse" id="main-nav-collapse">
                <ul class="nav navbar-nav">
                    @php $level = Auth::user()->nivel; @endphp

                    <li class="{{ Request::is('venta*') ? 'active' : '' }}">
                        <a href="{{ url('/venta') }}" class="nav-link-premium"><i class="fa fa-shopping-cart"></i> Ventas</a>
                    </li>

                    @if($level >= 10)
                    <li class="{{ Request::is('stock*') ? 'active' : '' }}">
                        <a href="{{ url('/stock') }}" class="nav-link-premium"><i class="fa fa-archive"></i> Productos</a>
                    </li>
                    <li class="{{ Request::is('StockPendiente*') ? 'active' : '' }}">
                        <a href="{{ url('/StockPendiente') }}" class="nav-link-premium">Pendientes</a>
                    </li>
                    <li class="{{ Request::is('StockPack*') ? 'active' : '' }}">
                        <a href="{{ url('/StockPack') }}" class="nav-link-premium">Packs</a>
                    </li>
                    <li class="{{ Request::is('Caja*') ? 'active' : '' }}">
                        <a href="{{ url('/Caja') }}" class="nav-link-premium"><i class="fa fa-university"></i> Caja</a>
                    </li>

                    @endif

                    <li class="{{ Request::is('ResumenVenta*') ? 'active' : '' }}">
                        <a href="{{ url('/ResumenVenta') }}" class="nav-link-premium"><i class="fa fa-line-chart"></i> Resumen</a>
                    </li>

                    @if($level >= 10)
                    <li class="{{ Request::is('CierreDia*') ? 'active' : '' }}">
                        <a href="{{ url('/CierreDia') }}" class="nav-link-premium">Cierre</a>
                    </li>
                    <li class="{{ Request::is('Configuracion*') ? 'active' : '' }}">
                        <a href="{{ url('/Configuracion') }}" class="nav-link-premium"><i class="fa fa-cog"></i></a>
                    </li>
                    @endif

                    <li class="{{ Request::is('Devolucion*') ? 'active' : '' }}">
                        <a href="{{ url('/Devolucion') }}" class="nav-link-premium text-danger">Devoluciones</a>
                    </li>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle nav-link-premium" data-toggle="dropdown" role="button">
                            <i class="fa fa-user-circle"></i> {{ Auth::user()->nombres }} <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ url('/logout') }}"><i class="fa fa-sign-out"></i> Salir</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endif

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert-premium').fadeOut();
            }, 5000);
        });
    </script>
</body>

</html>