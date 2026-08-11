<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait SyncsToRemote
{
    /**
     * Bandera en memoria para evitar sincronización circular
     */
    public $isSyncing = false;

    public static function bootSyncsToRemote()
    {
        // 1. Antes de guardar en la BD local, asignamos estado pendiente.
        static::saving(function ($model) {
            // Si $isSyncing es false, significa que el cambio fue local y debemos enviarlo
            if (!$model->isSyncing) {
                $model->actualizacion_estado = 'pendiente';
                // Si requieres forzar ultima_actualizacion usando timestamps:
                // $model->ultima_actualizacion = now();
            }
        });

        // 2. Después de que ya se guardó con éxito en tu BD local, lo enviamos al otro servidor.
        static::saved(function ($model) {
            if (!$model->isSyncing && env('SYNC_TARGET_URL')) {
                $model->enviarAlServidorRemoto();
            }
        });
    }

    public function enviarAlServidorRemoto()
    {
        $targetUrl = env('SYNC_TARGET_URL');
        $token = env('SYNC_TOKEN');

        if (!$targetUrl || !$token) {
            return;
        }

        try {
            // Disparamos la petición post con un timeout de 3 seg para no colgar la página en caso de un fallo en red
            $response = Http::withHeaders([
                'X-Sync-Token' => $token,
                'Accept' => 'application/json'
            ])->timeout(3)->post(rtrim($targetUrl, '/') . '/api/sincronizar', [
                        'model' => get_class($this),
                        'primary_key_name' => $this->getKeyName(),
                        'primary_key_value' => $this->getKey(),
                        'data' => $this->getAttributes(), // Mandamos todo el registro mapeado en arreglo crudo
                    ]);

            if ($response->successful()) {
                // Actualizamos directamente por Query Builder para no disparar eventos Eloquent nuevamente ("saving", "saved")
                DB::table($this->getTable())
                    ->where($this->getKeyName(), $this->getKey())
                    ->update(['actualizacion_estado' => 'sincronizado']);

                // Actualiza en memoria el objeto.
                $this->actualizacion_estado = 'sincronizado';
            } else {
                Log::warning("Sincronización rechazada (status {$response->status()}) para " . get_class($this) . " ID: " . $this->getKey());
            }
        } catch (\Exception $e) {
            Log::error("Fallo de conexión al sincronizar " . get_class($this) . " ID: " . $this->getKey() . " - " . $e->getMessage());
            // No hacemos nada mas. Como su estado se seteó a 'pendiente' en el método "saving", será repescado por el Cron.
        }
    }
}