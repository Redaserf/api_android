<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Recorrido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdafruitController extends Controller
{

    public function obtenerDatos()
    {
        $url = "https://io.adafruit.com/api/v2/Aldebaran0987Integradora/groups/default/feeds";
        $apiKey = config("adafruit_token.key");

        try {
            $response = Http::withHeaders([
                'X-AIO-Key' => $apiKey,
            ])->get($url);

            if ($response->successful()) {
                $feeds = $response->json();

                $result = collect($feeds)->map(function ($feed) {
                    return [
                        'key' => $feed['key'] ?? null,
                        'last_value' => $feed['last_value'] ?? null,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener los feeds',
                    'status' => $response->status(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un problema al realizar la solicitud',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    
}