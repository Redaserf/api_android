<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalculosController extends Controller
{
    //

    public function calcularDatosGuardarRecorridoEnMongo(Request $request)
    {
        
        $validaciones = Validator::make($request->all(), [
            'temperatura' => 'required',
            'humedad' => 'required',
            'luz_analogica' => 'required',
            'acelerometro' => 'required',
            'giroscopio' => 'required',
        ]);
        

    
    }

}