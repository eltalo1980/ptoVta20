<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ParametriaController;

class SyncDirectCommand extends Command
{
    protected $signature = 'sync:run';
    protected $description = 'Busca pendientes en BD directa y los transporta al otro servidor';

    public function handle()
    {
        $targetUrl = env('SYNC_TARGET_URL');
        $token = env('SYNC_TOKEN');

        if (!$targetUrl || !$token) {
            $this->warn("Faltan configurar variables SYNC en el .env");
            return 0;
        }

        $parametria = new ParametriaController();

        // AQUÍ mapeamos cada tabla producida por tu controlador con su "Columna de Llave Primaria"
        $tablasAMonitorear = [
            $parametria->fncTraeTablaCaja() => 'id', // Reemplaza 'id' con la Primary Key real
            $parametria->fncTraeTablaConsultaPrecio() => 'id',
            $parametria->fncTraeTablaDevolucionDetalleTmp() => 'id',
            $parametria->fncTraeTablaVentas() => 'id_ventas', // Ejemplo
            $parametria->fncTraeTablaStock() => 'id',
            $parametria->fncTraeTablaStockPack() => 'id',
            $parametria->fncTraeTablaStockPendiente() => 'id',
            // Agrega el resto de las que necesites que te devuelva ParametriaController
        ];

        foreach ($tablasAMonitorear as $tabla => $nombrePK) {

            if (!$tabla)
                continue; // Por si alguna función retorna nulo

            // Buscamos directamente con Query Builder
            $pendientes = DB::table($tabla)->where('actualizacion_estado', 'pendiente')->get();

            if ($pendientes->isNotEmpty()) {
                $this->info("Procesando {$pendientes->count()} registros pendientes en {$tabla}");

                foreach ($pendientes as $fila) {
                    $datos = (array) $fila;

                    try {
                        $response = Http::withHeaders([
                            'X-Sync-Token' => $token,
                            'Accept' => 'application/json'
                        ])->timeout(3)->post(rtrim($targetUrl, '/') . '/api/sincronizar-db', [
                                    'table' => $tabla,
                                    'pk_name' => $nombrePK,
                                    'pk_value' => $datos[$nombrePK],
                                    'data' => $datos,
                                ]);

                        if ($response->successful()) {
                            // Actualizamos el estado para no volver a enviarlo usando nombrePK dinámica
                            DB::table($tabla)
                                ->where($nombrePK, $datos[$nombrePK])
                                ->update(['actualizacion_estado' => 'sincronizado']);
                        } else {
                            Log::warning("Rechazo enviando a [{$tabla}]: status {$response->status()}");
                        }
                    } catch (\Exception $e) {
                        Log::error("Fallo de red en SyncDirectCommand. Tabla [{$tabla}] : " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Ciclo de sincronización completado.");
        return 0;
    }
}
