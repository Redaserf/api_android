<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    // =====[ Registro de usuario ]=====

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'apellido' => 'required|string|max:100',
            'correo' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 50 caracteres.',
            'apellido.required' => 'El campo apellido es obligatorio.',
            'apellido.max' => 'El nombre no puede exceder los 100 caracteres.',
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'correo.max' => 'El correo no puede exceder los 255 caracteres.',
            'correo.unique' => 'El correo ya está registrado.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos.',
                'errores' => $validator->errors()
            ], 400);
        }

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'correo' => $request->correo,
            'password' => Hash::make($request->password),
            // 'rol_id' => 1 (se supone que debe ser guest)
        ]);

        return response()->json([
            'mensaje' => 'Usuario creado con éxito.',
            'usuario' => $user
        ], 201);
    }


    // =====[ Inicio de sesión ]=====

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|string|email',
            'password' => 'required|string',
        ], [
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.email' => 'El correo no es válido.',
            'password.required' => 'El campo contraseña es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos.',
                'errores' => $validator->errors()
            ], 400);
        }

        if (!Auth::attempt($request->only('correo', 'password'))) {
            return response()->json([
                'mensaje' => 'Credenciales inválidas'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token
        ], 200);
    }
}
