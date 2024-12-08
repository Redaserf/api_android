<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Velocidad;
use Illuminate\Http\Request;

class VelocidadController extends Controller
{
    public function eliminarVelocidades(Request $request)
    {
        $request->validate([
            'recorrido_id' => 'required|exists:recorridos,id',
        ]);
    
        $recorridoId = $request->recorrido_id;
    
        try {
            Velocidad::where('recorrido_id', $recorridoId)->delete();
    
            return response()->json([
                'message' => "Se eliminaron las velocidades con éxito.",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hubo un problema al eliminar las velocidades.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
