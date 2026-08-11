@extends('layouts.app')

@section('content')
<div class="container" style="margin-top: 30px;">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default border-none shadow-premium">
				<div class="panel-heading bg-success-gradient p-25">
					<h3 class="panel-title text-white">
						<i class="fa fa-plus-circle" aria-hidden="true"></i> Crear Nueva Variable de Configuración
					</h3>
				</div>
				<div class="panel-body p-40">
					@if ($errors->any())
					<div class="alert alert-danger shadow-sm mb-20">
						<strong><i class="fa fa-exclamation-circle"></i> Error:</strong>
						<ul class="mb-0 mt-10">
							@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
					@endif

					<form action="{{ route('Configuracion.store') }}" method="POST">
						@csrf
						<input type="hidden" name="accion" value="crearVariable">
						<input type="hidden" name="id_local" value="{{ Auth::user()->id_local }}">

						<div class="row mb-20">
							<div class="col-md-12 form-group">
								<label class="label-custom">Nombre de Variable</label>
								<input type="text" name="variable" placeholder="Ej: stockMinimo" class="form-control input-rounded shadow-xs" required>
								<p class="help-block small text-muted">Evite espacios y caracteres especiales.</p>
							</div>
						</div>

						<div class="row mb-20">
							<div class="col-md-6 form-group">
								<label class="label-custom">Tipo de Valor</label>
								<select name="cmbTipoPregunta" id="cmbTipoPregunta" class="form-control input-rounded shadow-xs" required>
									<option value="truefalse">Booleano (Activo/Inactivo)</option>
									<option value="entero">Entero</option>
									<option value="texto">Texto</option>
									<option value="porcentaje">Porcentaje</option>
									<option value="objectSize">Tamaño (Chico, Mediano, Grande)</option>
								</select>
							</div>
							<div class="col-md-6 form-group">
								<label class="label-custom">Valor Inicial</label>
								<input type="text" name="valor" class="form-control input-rounded shadow-xs" required>
							</div>
						</div>

						<div class="form-group mb-30">
							<label class="label-custom">Descripción</label>
							<textarea name="descripcion" rows="3" class="form-control input-rounded-lg shadow-xs" placeholder="Describa para qué sirve esta variable..."></textarea>
						</div>

						@if(Auth::user()->nivel >= 100)
						<div class="row mb-30">
							<div class="col-md-6 form-group">
								<label class="label-custom">Nivel de Acceso</label>
								<select name="nivel" class="form-control input-rounded shadow-xs" required>
									<option value="10">Supervisor (10)</option>
									<option value="100">Administrador (100)</option>
								</select>
								<p class="help-block small text-muted">¿Quién podrá ver y editar esta variable?</p>
							</div>
						</div>
						@endif

						<hr class="mt-30 mb-30">

						<div class="row">
							<div class="col-xs-6">
								<a href="{{ route('Configuracion.index') }}" class="btn btn-default btn-lg btn-block btn-rounded-custom shadow-sm">
									<i class="fa fa-arrow-left"></i> Volver
								</a>
							</div>
							<div class="col-xs-6 text-right">
								<button type="submit" class="btn btn-success btn-lg btn-block btn-rounded-custom shadow-md">
									<i class="fa fa-save"></i> Guardar Variable
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	/* Custom spacing and typography */
	.p-25 {
		padding: 25px !important;
	}

	.p-40 {
		padding: 40px !important;
	}

	.mb-20 {
		margin-bottom: 20px !important;
	}

	.mb-30 {
		margin-bottom: 30px !important;
	}

	.mt-10 {
		margin-top: 10px !important;
	}

	.mt-30 {
		margin-top: 30px !important;
	}

	/* Modern Colors and Gradients */
	.bg-success-gradient {
		background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
		border-bottom: none !important;
	}

	.text-white {
		color: #ffffff !important;
	}

	.text-muted {
		color: #858796 !important;
	}

	/* Shadows */
	.shadow-premium {
		box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
	}

	.shadow-sm {
		box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
	}

	.shadow-md {
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
	}

	.shadow-xs {
		box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075) !important;
	}

	/* Borders */
	.border-none {
		border: none !important;
	}

	/* Label styling */
	.label-custom {
		display: block;
		margin-bottom: 8px;
		font-weight: 700;
		color: #4e73df;
		font-size: 14px;
	}

	/* Input styling */
	.input-rounded {
		border-radius: 8px !important;
		height: 45px !important;
		border: 1px solid #e3e6f0 !important;
		background-color: #f8f9fc !important;
		transition: all 0.2s ease;
	}

	.input-rounded:focus {
		background-color: #fff !important;
		border-color: #bac8f3 !important;
		box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1) !important;
		outline: 0;
	}

	.input-rounded-lg {
		border-radius: 12px !important;
		border: 1px solid #e3e6f0 !important;
		background-color: #f8f9fc !important;
		padding: 10px 15px !important;
	}

	/* Buttons */
	.btn-rounded-custom {
		border-radius: 50px !important;
		font-weight: 600 !important;
		padding-top: 12px !important;
		padding-bottom: 12px !important;
		transition: all 0.3s ease;
	}

	.panel-title {
		font-size: 20px;
		font-weight: 700;
	}
</style>
@endsection