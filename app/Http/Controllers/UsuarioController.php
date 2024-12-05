<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function actualizar(Request $request)
    {
        $user = Auth::user();
    
        $validatedData = $request->validate([
            'nombre' => 'string|max:50|nullable',
            'apellido' => 'string|max:100|nullable',
            'peso' => 'numeric|between:20,150|nullable',
            'email' => 'string|email|max:255|nullable',
        ]);
    
        Log::info('Datos validados: ', $validatedData);
    
        $user->update(array_filter($validatedData));
    
        Log::info('Nuevo peso del usuario: ' . $user->peso);
    
        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'usuario' => [
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'peso' => $user->peso,
                'email' => $user->email,
            ],
        ], 200);
    }    

    public function show(Request $request)
    {
        // usé el log y el request pa ver si se enviaba el token, ja
        Log::info('Authorization Header: ' . $request->header('Authorization'));
    
        $user = Auth::user();
    
        if (!$user) {
            Log::warning('Usuario no encontrado. Token inválido o no enviado.');
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 401);
        }
    
        Log::info('Usuario encontrado: ' . $user->email);
    
        return response()->json([
            'usuario' => [
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'peso' => $user->peso
            ],
        ], 200);
    }
    
    
}
