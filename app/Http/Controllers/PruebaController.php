<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PruebaController extends Controller
{
    public function pruebaDeConexion(Request $request)
    {
        return response()->json([
            "response" => $request->all()
        ], 200);
    }


    public function jsonRaspberry(Request $request)
    {
        $validaciones = Validator::make($request->all(), [
            'temperatura' => 'required',
            'humedad' => 'required',
            'luz_analogica' => 'required',
            'acelerometro' => 'required',
            'giroscopio' => 'required',
        ], [
            'temperatura.required' => 'La temperatura es requerida',
            'humedad.required' => 'La humedad es requerida',
            'luz_analogica.required' => 'La luz analógica es requerida',
            'acelerometro.required' => 'El acelerómetro es requerido',
            'giroscopio.required' => 'El giroscopio es requerido',
        ]);

        if ($validaciones->fails()) {
            return response()->json([
                "msg" => "Error en la validación de datos",
                "errors" => $validaciones->errors()
            ], 422);
        }



        return response()->json([
            "msg"=>"Datos recibidos corrrectamente",
            "data"=>$request->all()
        ],200);
    }


    public function simulacionRecorrido(Request $request)
    {

        $validaciones = Validator::make($request->all(), [
            'bici_id' => 'required',
            'temperatura' => 'required',
            'humedad' => 'required',
            'luz_analogica' => 'required',
            'acelerometro' => 'required',
            'giroscopio' => 'required',
        ], [
            'bici_id.required' => 'El id del recorrido es requerido',
            'temperatura.required' => 'La temperatura es requerida',
            'humedad.required' => 'La humedad es requerida',
            'luz_analogica.required' => 'La luz analógica es requerida',
            'acelerometro.required' => 'El acelerómetro es requerido',
            'giroscopio.required' => 'El giroscopio es requerido',
        ]);

        if($validaciones->fails()){
            return response()->json([
                'message' => 'Datos incorrectos',
                'errors' => $validaciones->errors()
            ], 422);
        }

        $recorrido = Recorrido::where('_id', '67e5aeded3fa2a015a089212')->first();

        if(!$recorrido){
            return response()->json([
                'message' => 'Recorrido no encontrado'
            ], 404);
        }

        $x = $request->acelerometro[0];
        $y = $request->acelerometro[1];
        $z = $request->acelerometro[2];

        $velocidad = $this->calcularVelocidad($x, $y, $z);

        $recorrido->velocidad = $velocidad;
        if($recorrido->velocidad_maxima < $velocidad){
            $recorrido->velocidad_maxima = $velocidad;
        }//si la velocidad actual es mayor a la maxima se actualiza la maxima
        $recorrido->suma_velocidad['suma'] += $velocidad;
        $recorrido->suma_velocidad['cantidad'] += 1;

        $recorrido->velocidad_promedio = $recorrido->suma_velocidad['suma'] / $recorrido->suma_velocidad['cantidad'];//se calcula el promedio de las velocidades
        $recorrido->save();

        return response()->json([
            'message' => 'Datos guardados correctamente'
        ], 200);
    }

    private function calcularVelocidad($ax, $ay, $az)
    {
        Log::info("Valores de acelerómetro: X={$ax}, Y={$ay}, Z={$az}");

        $ax = floatval($ax);
        $ay = floatval($ay);
        $az = floatval($az);

        $aceleracion = sqrt(pow($ax, 2) + pow($ay, 2) + pow($az, 2));

        return $aceleracion * 3.6;
    }


}
