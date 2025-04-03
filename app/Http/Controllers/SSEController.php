<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SSEController extends Controller
{
    //

    public function recorridoActivo(Request $request)
    {
       
        $validaciones = Validator::make($request->all(), [
            'usuarioId' => 'required|exists:usuarios,id',
        ]);
        
        if ($validaciones->fails()) {
            return response()->json([
                'errors' => $validaciones->errors(),
            ], 422);
        }

        $user = Usuario::where('id', $request->usuarioId)->first();

        if (!$user) {
            return response()->json([
                'errors' => true,
                'message' => 'Usuario no encontrado',
            ], 404);
        }
        

        return response()->stream(function () use ($user) {
            $recorridoActivo = Recorrido::raw(function ($collection) use ($user) {
                return $collection->findOne([
                    'usuario._id' => $user->id,
                    'acabado' => false,
                ]);
            });

            if ($recorridoActivo) {
                echo "event: recorrido-activo\n";
                echo "data: " . json_encode([
                    'activo' => true,
                    'recorridoId' => $recorridoActivo['_id'],
                    'usuarioId' => $recorridoActivo['usuario']['_id'],
                ]) . "\n\n";
            } else {
                echo "event: recorrido-activo\n";
                echo "data: " . json_encode([
                    'activo' => false,
                ]) . "\n\n";
            }

            ob_flush();
            flush();
            sleep(5); // espera 5 segundos antes de volver a verificar
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);

    }
}
