<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return response()->json($usuario, 200);
    }
    
    //consultas para graficas y demas estadisticas en vista de admin
    public function usuarioConMasKilometrosRecorridos(){//esto se va a sacar de mongodb
                                                    //es para probar solamente
        $usuarioConMasKilometrosYlaDistanciaRecorrida = Recorrido::raw(function($collection)
        {
            return $collection->aggregate([
                [
                    '$match' => ['usuario.rol_id' => 2]//usurios con rol usuario
                ],
                [
                    '$group' => [
                        '_id' => '$usuario._id',//agrupar para sacar la distancia total recorrida por usuario
                        'total' => ['$sum' => '$distancia_recorrida']
                    ]
                ],
                [
                    '$sort' => ['total' => -1]
                ],
                [
                    '$limit' => 1//solo el usuario con mas distancia recorrida
                ]
            ]);
        });

        $usuario = Usuario::find($usuarioConMasKilometrosYlaDistanciaRecorrida[0]->_id);
        
        $respuestaConKilometrosRecorridos = [
            'usuario' => $usuario,
            'kilometros_recorridos' => $usuarioConMasKilometrosYlaDistanciaRecorrida[0]->total
        ];

        return response()->json($respuestaConKilometrosRecorridos);
    }


    public function recorridoConMasDistancia(){//este no tiene Ruta pai ponsela despues
        $recorridoConMasDistancia = Recorrido::orderBy('distancia_recorrida', 'desc')->first();

        return response()->json($recorridoConMasDistancia);
    }

}
