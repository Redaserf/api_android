<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\CreaciondeCuenta;
use App\Mail\emailRestablcerContra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

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
            'password.required' => 'El campo contraseña es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos.',
                'errores' => $validator->errors()
            ], 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
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
            'email' => 'required|email|max:255|unique:usuarios',
            'password' => 'required|min:8',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 50 caracteres.',
            'apellido.required' => 'El campo apellido es obligatorio.',
            'apellido.max' => 'El nombre no puede exceder los 100 caracteres.',
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
            ], 400);
        }

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'rol_id' => 1 (se supone que debe ser guest)
        ]);


        // Enviar email de activacion
        $this->sendActivationEmail($user);

        return response()->json([
            'mensaje' => 'Usuario creado con éxito.',
            'usuario' => $user
        ], 201);
    }


    // =====[ Envio de email ]=====

    public function sendActivationEmail(Usuario $user)
{
    $url = URL::temporarySignedRoute(
        'activation.verify', now()->addMinutes(60), ['id' => $user->id]
    );

    $email = $user->email;

    try {
        Mail::to($user->email)->send(new CreaciondeCuenta($email, $url));
    } catch (\Exception $e) {
        return response()->json(['error' => 'No se pudo enviar el email: ' . $e->getMessage()], 500);
    }
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
