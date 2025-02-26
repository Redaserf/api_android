<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //


    public function todosLosUsuarios()
    {
        $usuarios = Usuario::where('rol_id', 2)->get();

        return response()->json($usuarios);
    }


    public function showUsuarioConBicicleta($id){

        $usuario = Usuario::findOrFail($id);
        if($usuario){
            $usuario->load('bicicletas');
        }

        return response()->json($usuario);
    }


}
