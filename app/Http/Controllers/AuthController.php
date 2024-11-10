<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\CreaciondeCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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

      // =====[ Log out ]=====


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Cierre de sesión exitoso.'
        ], 200);
    }

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


        // Enviar correo de activacion
        $this->sendActivationEmail($user);

        return response()->json([
            'mensaje' => 'Usuario creado con éxito.',
            'usuario' => $user
        ], 201);
    }


    // =====[ Envio de correo ]=====

    public function sendActivationEmail(User $user){
        $url = URL::temporarySignedRoute(
            'activation.verify', now()->addMinutes(60), ['id' => $user->id]
        );

        $correo = $user->correo;

        try {
            Mail::to($user->correo)->send(new CreaciondeCuenta($url,$correo));
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo enviar el correo: ' . $e->getMessage()], 500);
        }

    }


     // =====[ Activacion de la cuenta ]=====


    public function activate($id, Request $request)
    {
        $user = User::findOrFail($id);

        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Enlace de activación inválido o expirado.'], 403);
        }
       
        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Cuenta activada exitosamente.']);
    }



}
