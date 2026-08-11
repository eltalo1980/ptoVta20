<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDBController extends Controller
{
    public function receive(Request $request)
    {
        $token = env('SYNC_TOKEN');
        if ($request->header('X-Sync-Token') !== $token || empty($token)) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $tabla = $request->input('table');
        $pkName = $request->input('pk_name');
        $pkValue = $request->input('pk_value');
        $data = $request->input('data');

        if (!$tabla || !$pkName) {
            return response()->json(['error' => 'Petición incorrecta'], 400);
        }

        try {
            // Lo recibimos e insertamos forzándolo primero a estado sincronizado
            $data['actualizacion_estado'] = 'sincronizado';

            // Al inyectarse con updateOrInsert se salta la necesidad de un Modelo, trabajando directamente en tu tabla final.
            DB::table($tabla)->updateOrInsert(
                [$pkName => $pkValue],
                $data
            );

            return response()->json(['message' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error("Error sincronizando (DB::table) tabla $tabla : " . $e->getMessage());
            return response()->json(['error' => 'Error 500 interno'], 500);
        }
    }
}
