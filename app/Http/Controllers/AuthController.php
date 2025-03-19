<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\CreaciondeCuenta;
use App\Mail\emailRestablcerContra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use User;

class AuthController extends Controller
{
    // =====[ Inicio de sesión ]=====

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email no es válido.',
            'password.required' => 'El campo contraseña es obligatorio.'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Credenciales inválidas.',
                'errores' => $validator->errors()
            ], 422);
        }
    
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'mensaje' => 'Credenciales inválidas'
            ], 401);
        }
    
        $user = Auth::user();
        $user->peso = (float) $user->peso;
        $user->estatura = (float) $user->estatura;
    
        // Verificar si el usuario aún no ha verificado su correo
        if ($user->email_verified_at == null) {
            return response()->json([
                'mensaje' => 'El correo aún no ha sido verificado. Por favor, ingresa el código de verificación.',
                'redirect' => 'verify_code',
                'email' => $user->email,
            ], 403);
        }
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'token' => $token,
            'message' => 'Sesión iniciada con éxito',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'peso' => $user->peso,       
                'estatura' => $user->estatura,
                'email' => $user->email,
                'rol_id' => $user->rol_id,
                'email_verified_at' => $user->email_verified_at,
                'deleted_at' => $user->deleted_at
            ]
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
            'peso' => 'required|numeric|between:20,150',
            'estatura' => 'required|numeric|between:1.10,2.20',
            'email' => 'required|email|max:255|unique:usuarios',
            'password' => 'required|min:8',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 50 caracteres.',
            'apellido.required' => 'El campo apellido es obligatorio.',
            'apellido.max' => 'El nombre no puede exceder los 100 caracteres.',
            'peso.required' => 'El campo peso es obligatorio.',
            'peso.numeric' => 'El peso debe ser un número.',
            'peso.between' => 'El peso debe estar entre 20kg y 150kg.',
            'estatura.required' => 'El campo estatura es obligatorio.',
            'estatura.numeric' => 'La estatura debe ser un número.',
            'estatura.between' => 'La estatura debe estar entre 1.10m y 2.20m.',
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email no es válido.',
            'email.max' => 'El email no puede exceder los 255 caracteres.',
            'email.unique' => 'El email ya está registrado.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos.',
                'errores' => $validator->errors()
            ], 422);
        }

        $codigo = rand(100000, 999999);

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'peso' => $request->peso,
            'estatura' => $request->estatura,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'codigo' => $codigo,
            'rol_id' => 1
        ]);

        // Enviar email de activacion
        $this->sendActivationEmail($user, $codigo);

        return response()->json([
            'mensaje' => 'Usuario creado con éxito.',
            'usuario' => $user
        ], 201);
    }
    

    // =====[ Verificar correo ]=====

    public function verificarCodigo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:usuarios,email',
            'codigo' => 'required|numeric|digits:6',
        ], [
            'email.required' => 'El campo email es obligatorio.',
            'email.exists' => 'El usuario con este email no existe.',
            'codigo.required' => 'El campo código es obligatorio.',
            'codigo.numeric' => 'El código debe ser un número.',
            'codigo.digits' => 'El código debe tener exactamente 6 dígitos.',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos.',
                'errores' => $validator->errors(),
            ], 422);
        }
    
        $user = Usuario::where('email', $request->email)->firstOrFail();
    
        if ($user->codigo !== $request->codigo) {
            return response()->json([
                'mensaje' => 'El código proporcionado es incorrecto.',
            ], 400);
        }
    
        $user->email_verified_at = now();
        $user->rol_id = 2;
        $user->codigo = null;
        $user->save();
    
        // Crear un token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'mensaje' => 'Código verificado correctamente. El usuario ahora está activo.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
            ],
        ], 200);
    }    


    // =====[ Envío de correo ]=====

    public function sendActivationEmail(Usuario $user, $codigo){

        $correo = $user->email;

        try {
            Mail::to($user->email)->send(new CreaciondeCuenta($correo, $codigo));
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo enviar el correo: ' . $e->getMessage()], 500);
        }
    }


    public function reenviar(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ], [
            'email.required' => 'El campo email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.exists' => 'No se encontró un usuario con este email.',
        ]);
    
        $email = $request->email;
    
        $user = Usuario::where('email', $email)->first();
    
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'mensaje' => 'El correo ya ha sido verificado anteriormente.'
            ], 200);
        }
    
        $codigo = rand(100000, 999999);
    
        try {
            Mail::to($user->email)->send(new CreaciondeCuenta($user->email,$codigo));
    
            $user->codigo = $codigo;
            $user->save();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    
        return response()->json([
            'mensaje' => 'Se ha reenviado el correo de verificación.',
        ], 200);
    }
    


     // =====[ Activacion de la cuenta ]=====

    public function activate($id, Request $request)
    {
        $user = Usuario::findOrFail($id);

        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Enlace de activación inválido o expirado.'], 403);
        }
       
        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Cuenta activada exitosamente.']);
    }

   // =====[ Contraseña olvidada ]=====

   public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
    ], [
        'email.required' => 'El campo email es obligatorio.',
        'email.email' => 'El email no es válido.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'mensaje' => 'Error en la validación de los datos.',
            'errores' => $validator->errors()
        ], 400);
    }

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'mensaje' => 'El enlace de restablecimiento de contraseña ha sido enviado a tu email electrónico.'
        ], 200);
    } else {
        return response()->json([
            'mensaje' => 'No se pudo enviar el enlace de restablecimiento. Inténtalo de nuevo más tarde.'
        ], 500);
    }
}


public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'required|string|min:8|confirmed',
        'token' => 'required|string',
    ], [
        'email.required' => 'El campo correo es obligatorio.',
        'email.email' => 'El correo no es válido.',
        'password.required' => 'El campo contraseña es obligatorio.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        'token.required' => 'El token de restablecimiento es obligatorio.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'mensaje' => 'Error en la validación de los datos.',
            'errores' => $validator->errors()
        ], 400);
    }

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return response()->json([
            'mensaje' => 'La contraseña ha sido restablecida exitosamente.'
        ], 200);
    } else {
        return response()->json([
            'mensaje' => 'No se pudo restablecer la contraseña. Inténtalo de nuevo.'
        ], 500);
    }
}
//


}
