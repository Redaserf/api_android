<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Recorrido;
use App\Models\Velocidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdafruitController extends Controller
{
    public function obtenerDatos(Request $request)
    {
        $url = "https://io.adafruit.com/api/v2/Aldebaran0987Integradora/groups/default/feeds";
        $apiKey = config("adafruit_token.key");

        $request->validate([
            'recorrido_id' => 'required|exists:recorridos,id',
            'tiempo' => 'required|string',
        ]);

        $recorridoId = $request->recorrido_id;
        $tiempo = $request->tiempo;
        $usuario = auth()->user();
        $pesoUsuario = $usuario->peso;

        Log::info("Tiempo traído de Android: {$tiempo}");
        Log::info("Peso del usuario: {$pesoUsuario}");

        try {
            // =====[ Obtener datos de Adafruit ]=====
            $response = Http::withHeaders([
                'X-AIO-Key' => $apiKey,
            ])->get($url);

            if ($response->successful()) {
                $feeds = $response->json();

                $temperatura = $this->getFeedValue($feeds, 'temperatura');
                $acelerometroX = $this->getFeedValue($feeds, 'acelerometro-x');
                $acelerometroY = $this->getFeedValue($feeds, 'acelerometro-y');
                $acelerometroZ = $this->getFeedValue($feeds, 'acelerometro-z');

                if ($acelerometroX !== null && $acelerometroY !== null && $acelerometroZ !== null) {
                    $velocidad = $this->calcularVelocidad($acelerometroX, $acelerometroY, $acelerometroZ);


                    Log::info("Velocidad calculada: {$velocidad}");
                    Log::info("Temperatura obtenida: {$temperatura}");

                    Velocidad::create([
                        'recorrido_id' => $recorridoId,
                        'valor' => $velocidad,
                    ]);

                    [$horas, $minutos, $segundos] = explode(':', $tiempo);
                    $tiempoSegundos = ($horas * 3600) + ($minutos * 60) + $segundos;
                    $distanciaIncremental = $this->calcularDistanciaIncremental($velocidad, 5); // Intervalo de 5 segundos
                    $tiempoHoras = $tiempoSegundos / 3600;
                    $caloriasQuemadas = $this->calcularCalorias($pesoUsuario, $velocidad, $tiempoHoras);

                    $this->actualizarVelocidadesRecorrido($recorridoId, $tiempo, $distanciaIncremental, $caloriasQuemadas, $temperatura);

                    $recorrido = Recorrido::findOrFail($recorridoId);

                    return response()->json([
                        'success' => true,
                        'message' => 'Datos obtenidos y procesados correctamente.',
                        'velocidad_actual' => $velocidad,
                        'velocidad_maxima' => $recorrido->velocidad_maxima,
                        'velocidad_promedio' => $recorrido->velocidad_promedio,
                        'distancia_recorrida' => $recorrido->distancia_recorrida,
                        'calorias' => $caloriasQuemadas,
                        'temperatura' => $temperatura,
                    ], 200);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se encontraron datos de acelerómetro válidos.',
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener los feeds de Adafruit',
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

    // =====[ Calcular las calorías quemadas según velocidad y peso ]=====
    private function calcularCalorias($pesoUsuario, $velocidadPromedio, $tiempoHoras)
    {
        if ($velocidadPromedio <= 8) {
            $met = 4.0;
        } elseif ($velocidadPromedio <= 16) {
            $met = 6.8;
        } elseif ($velocidadPromedio <= 19) {
            $met = 8.0;
        } elseif ($velocidadPromedio <= 22) {
            $met = 10.0;
        } elseif ($velocidadPromedio <= 25) {
            $met = 12.0;
        } else {
            $met = 15.8;
        }

        return $met * $pesoUsuario * $tiempoHoras;
    }

    // =====[ Obtener el último valor de un feed ]=====
    private function getFeedValue($feeds, $key)
    {
        $feed = collect($feeds)->firstWhere('key', $key);
        return $feed['last_value'] ?? null;
    }

    // =====[ Calcular la velocidad a partir de los ejes X, Y y Z ]=====
    private function calcularVelocidad($ax, $ay, $az)
    {
        Log::info("Valores de acelerómetro: X={$ax}, Y={$ay}, Z={$az}");

        $ax = floatval($ax);
        $ay = floatval($ay);
        $az = floatval($az);

        $aceleracion = sqrt(pow($ax, 2) + pow($ay, 2) + pow($az, 2));

        return $aceleracion * 3.6;
    }


    // =====[ Calcular la distancia incremental en kilómetros ]=====
    private function calcularDistanciaIncremental($velocidadActual, $tiempoSegundos)
    {
        $velocidadEnMS = $velocidadActual / 3.6;
        $distanciaRecorrida = $velocidadEnMS * $tiempoSegundos;
        return $distanciaRecorrida / 1000; // Retornar en kilómetros
    }

    // =====[ Actualizar métricas acumulativas del recorrido ]=====
    private function actualizarVelocidadesRecorrido($recorridoId, $tiempo, $distanciaIncremental, $caloriasQuemadas, $temperatura)
    {
        $recorrido = Recorrido::findOrFail($recorridoId);

        $distanciaTotal = $recorrido->distancia_recorrida + $distanciaIncremental;

        $velocidades = Velocidad::where('recorrido_id', $recorridoId)->pluck('valor');

        if ($velocidades->isNotEmpty()) {
            $velocidadMaxima = $velocidades->max();
            $velocidadPromedio = $velocidades->avg();

            $recorrido->update([
                'velocidad_maxima' => $velocidadMaxima,
                'velocidad_promedio' => $velocidadPromedio,
                'distancia_recorrida' => $distanciaTotal,
                'tiempo' => $tiempo,
                'calorias' => $caloriasQuemadas,
                'temperatura' => $temperatura,
            ]);

            Log::info("Distancia actualizada: {$distanciaTotal}");
        }
    }
}
