<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{
    public function actualizar(Request $request)
    {
        try {
            $user = Auth::user();
    
            $validator = Validator::make($request->all(), [
                'nombre' => 'string|max:50|nullable',
                'apellido' => 'string|max:100|nullable',
                'peso' => 'numeric|between:20,150|nullable',
                'email' => [
                    'nullable',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('usuarios')->ignore($user->id),
                ],
            ], [
                'nombre.max' => 'El nombre no puede exceder los 50 caracteres.',
                'apellido.max' => 'El apellido no puede exceder los 100 caracteres.',
                'peso.numeric' => 'El peso debe ser un número.',
                'peso.between' => 'El peso debe estar entre 20kg y 150kg.',
                'email.email' => 'El email no es válido.',
                'email.max' => 'El email no puede exceder los 255 caracteres.',
                'email.unique' => 'El email ya está registrado.',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Errores en los datos enviados.',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $validatedData = $validator->validated();
    
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
        } catch (\Throwable $e) {
            Log::error('Error al actualizar el perfil: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el perfil.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    
    public function show(Request $request)
    {
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
