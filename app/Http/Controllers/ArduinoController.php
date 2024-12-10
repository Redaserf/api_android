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

        // dd($ip_arduino);
        $encender = $request->encender;

        $response = Http::asForm()->post($ip_arduino, [
            "encender" => $encender
        ]);



        return response()->json([
            'data' => $response->json()
        ]);

    }

}
