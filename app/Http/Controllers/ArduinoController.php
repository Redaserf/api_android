<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ArduinoController extends Controller
{
    //

    public function encenderMatriz(Request $request){

        $validaciones = Validator::make($request->all(), [
            'encender' => 'required|boolean'
        ], [
            'encender.required' => 'El valor encender es necesario',
            'encender.boolean' => 'El valor debe ser booleano'
        ]);
        $ip_arduino = config("ip_arduino.ip");

        if($validaciones->fails()){
            return response()->json([
                'errores' => $validaciones->errors()
            ]);
        }

  
      
        $response = Http::withHeaders([
            "X-AIO-Key" => config("adafruit_token.key")
        ])->put($ip_arduino, [
            'feed' => [
                        'description' => $request->encender
            ]  
        ]);

        if($response->successful()){

            if($response->json()['description'] == "1"){ 
                return response()->json([
                    'luz' => 1
                ]);
            }

            return response()->json([
                'luz' => 0
            ]);
        }else{
            return response()->json([
                'msg' => 'No se pudo editar el feed',
                'error' => $response->json()
            ], 500);
        }


        // $data = $response->json()['last_value'];


    }

}
