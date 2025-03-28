<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
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


}
