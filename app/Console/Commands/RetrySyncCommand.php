<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RetrySyncCommand extends Command
{
    protected $signature = 'sync:retry';
    protected $description = 'Reintenta enviar los registros con actualizacion_estado = pendiente por fallo de red';

    // IMPORTANTE: Asegúrate de colocar aquí los nombres correctos de tus modelos.
    protected $modelosASincronizar = [
        \App\Models\LocalMarcoCaja::class,
        \App\Models\LocalMarcoConsultaPrecio::class,
        \App\Models\LocalMarcoDevolucionDetalleTmp::class,
        \App\Models\LocalMarcoVentas::class,
        \App\Models\LocalMarcoStock::class,
        \App\Models\LocalMarcoStockPack::class,
        \App\Models\LocalMarcoStockPendiente::class,
        \App\Models\LocalMarcoVentasHistoria::class,
        \App\Models\LocalMarcoVentasTotal::class,
        \App\Models\LocalMarcoVentasTotalHistoria::class,
    ];

    public function handle()
    {
        $this->info("Iniciando reintento de sincronización...");
        $logs_enviados = 0;
        $logs_fallidos = 0;

        foreach ($this->modelosASincronizar as $modeloClass) {
            if (!class_exists($modeloClass)) {
                $this->warn("Advertencia: El modelo {$modeloClass} no existe");
                continue;
            }

            $pendientes = $modeloClass::where('actualizacion_estado', 'pendiente')->get();

            if ($pendientes->count() > 0) {
                $this->info("Tabla " . (new $modeloClass)->getTable() . ": {$pendientes->count()} a sincronizar.");

                foreach ($pendientes as $registro) {
                    $registro->enviarAlServidorRemoto(); // usamos la función del trait 

                    if ($registro->actualizacion_estado === 'sincronizado') {
                        $logs_enviados++;
                    } else {
                        $logs_fallidos++;
                    }
                }
            }
        }

        $this->info("Total éxitos: {$logs_enviados} - Total fallos: {$logs_fallidos}");
        return 0;
    }
}