<?php

namespace App\Http\Controllers;

use App\Events\RecorridoActivo;
use App\Models\Recorrido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CalculosController extends Controller
{
    //

    public function calcularDatosGuardarRecorridoEnMongo(Request $request)
    {

        $validaciones = Validator::make($request->all(), [
            'bicicleta_id' => 'required',
            'temperatura' => 'required',
            'humedad' => 'required',
            //'acelerometro' => 'required',
            //'giroscopio' => 'required',
        ], [
            'bicicleta_id.required' => 'El id de la bicicleta es requerido',
            'temperatura.required' => 'La temperatura es requerida',
            'humedad.required' => 'La humedad es requerida',
            //'acelerometro.required' => 'El acelerómetro es requerido',
            //'giroscopio.required' => 'El giroscopio es requerido',
        ]);

        if($validaciones->fails()){
            return response()->json([
                'message' => 'Datos incorrectos',
                'errors' => $validaciones->errors()
            ], 422);
        }

        $recorrido = Recorrido::where('bicicleta_id', $request->bicicleta_id)->where('acabado', false)->first();//el recorrido iniciado por id de la bicicleta q manda la rasp

        if(!$recorrido){
            return response()->json([
                'message' => 'Recorrido no encontrado'
            ], 404);
        }

        // $x = $request->acelerometro[0];
        // $y = $request->acelerometro[1];
        // $z = $request->acelerometro[2];

        // $velocidad = $this->calcularVelocidad($x, $y, $z, $recorrido);//se calcula la velocidad actual con los valores del acelerometro y la velocidad anterior




        // $recorrido->acelerometro = $request->acelerometro;
        // $recorrido->velocidad = $velocidad;

        // Log::info("Velocidad calculada: " . $velocidad);
        // Log::info("Valores de acelerómetro: X={$x}, Y={$y}, Z={$z}");

        // if($recorrido->velocidad_maxima < $velocidad){
        //     $recorrido->velocidad_maxima = $velocidad;
        // }//si la velocidad actual es mayor a la maxima se actualiza la maxima

        $recorrido->temperatura = $request->temperatura;
        // $recorrido->suma += $velocidad;
        // $recorrido->cantidad += 1;

        // $recorrido->velocidad_promedio = $recorrido->suma / $recorrido->cantidad;//se calcula el promedio de las velocidades
        $recorrido->save();

        return response()->json([
            'message' => 'Datos guardados correctamente'
        ], 200);
    }

    public function obtenerDatos(Request $request){


        $validaciones = Validator::make($request->all(), [
            'recorrido_id' => 'required',
            'tiempo' => 'required',
            'velocidad' => 'required',
        ], [
            'recorrido_id.required' => 'El id del recorrido es requerido',
            'tiempo.required' => 'El tiempo es requerido',
            'velocidad.required' => 'La velocidad es requerida',
        ]);


        if($validaciones->fails()){
            return response()->json([
                'message' => 'Datos incorrectos',
                'errors' => $validaciones->errors()
            ], 422);
        }

        $recorrido = Recorrido::find($request->recorrido_id);

        if(!$recorrido){
            return response()->json([
                'message' => 'Recorrido no encontrado'
            ], 404);
        }

        $recorrido->velocidad = $request->velocidad;
        $recorrido->suma += $request->velocidad;
        $recorrido->cantidad += 1;
        $recorrido->velocidad_promedio = $recorrido->suma / $recorrido->cantidad;//se calcula el promedio de las velocidades
        $recorrido->velocidad_maxima = max($recorrido->velocidad_maxima, $request->velocidad);//si la velocidad actual es mayor a la maxima se actualiza la maxima

        $recorrido->save();

        $tiempo = $request->tiempo;
        $pesoUsuario = $request->user()->peso;

        [$horas, $minutos, $segundos] = explode(':', $tiempo);
        $tiempoSegundos = ($horas * 3600) + ($minutos * 60) + $segundos;
        $distanciaIncremental = $this->calcularDistanciaIncremental($recorrido->velocidad, 5); // Intervalo de 5 segundos
        $tiempoHoras = $tiempoSegundos / 3600;
        $caloriasQuemadas = $this->calcularCalorias($pesoUsuario, $recorrido->velocidad_promedio, $tiempoHoras);

        $recorrido->tiempo = $tiempo;
        $recorrido->calorias += $caloriasQuemadas;
        $recorrido->distancia_recorrida += $distanciaIncremental;
        $pesoPerdidoKilogramos = $recorrido->calorias / 7700; //7000 calorias son 1 kilogramo perdido

        $recorrido->save();

        event(new RecorridoActivo($recorrido));

        return response()->json([
            'message' => 'Datos obtenidos y procesados correctamente.',
            'recorrido' => $recorrido,
            'peso_perdido' => $pesoPerdidoKilogramos,
        ]);
    }


    private function calcularCalorias(float $pesoUsuario, float $velocidadPromedio, float $tiempoHoras): float
    {
        // Determinar el valor MET según la velocidad
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

        return round($met * $pesoUsuario * $tiempoHoras, 2);
    }



    private function calcularDistanciaIncremental($velocidadActual, $tiempoSegundos)
    {
        $velocidadEnMS = $velocidadActual / 3.6;
        $distanciaRecorrida = $velocidadEnMS * $tiempoSegundos;
        return $distanciaRecorrida / 1000; // Retornar en kilómetros
    }


    public function calcularVelocidad($ax, $ay, $az, Recorrido $recorrido)
    {
        $ax = floatval($ax);
        $ay = floatval($ay);
        $az = floatval($az);
    
        $GRAVEDAD = 9.81;
        $az -= $GRAVEDAD;
    
        $deltaT = 2;
    
        $aceleracion = sqrt(pow($ax, 2) + pow($ay, 2) + pow($az, 2));
    
        if ($aceleracion < 0.1) {
            $aceleracion = 0;
        }
    
        $ultimaVelocidad = $recorrido->velocidad ?? 0;
    
        $nuevaVelocidad = $ultimaVelocidad + ($aceleracion * $deltaT);
        $nuevaVelocidad = max(0, $nuevaVelocidad); // evitar negativos
    
        $recorrido->velocidad = $nuevaVelocidad * 3.6;
    
        if ($recorrido->velocidad_maxima < $recorrido->velocidad) {
            $recorrido->velocidad_maxima = $recorrido->velocidad;
        }
    
        $recorrido->save();
    }
    

}
