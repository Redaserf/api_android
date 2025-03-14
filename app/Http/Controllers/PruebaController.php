<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;

class PruebaController extends Controller
{
    public function pruebaDeConexion(Request $request)
    {
        return response()->json([
            "response" => $request->all()
        ], 200);
    }


}
