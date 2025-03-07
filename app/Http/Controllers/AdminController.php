<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use App\Models\Usuario;
use Carbon\Carbon;
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


    public function showUsuarioConBicicletas($id){

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

    public function distanciaPorUsuario(){
        $distanciaPorUsuario = Recorrido::raw(function($collection){
            return $collection->aggregate([
                [
                    '$match' => [
                        'usuario.rol_id' => 2,//usuarios con rol usuario
                        'acabado' => false //Cambiar a true
                    ]
                ],
                [
                    '$group' => [
                        '_id' => '$usuario._id',
                        'total' => ['$sum' => '$distancia_recorrida']
                    ]
                ],
                [
                    '$sort' => ['total' => -1]
                ]
            ]);
        });

        return response()->json($distanciaPorUsuario);
    }

    public function porcentajeRecorridosPorBici(){
        $recorridosPorBici = Recorrido::raw(function($collection){
            return $collection->aggregate([
                [
                    '$group' => [
                        '_id' => '$bicicleta_id',
                        'total' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['total' => -1]
                ]
            ]);
        });

        return response()->json($recorridosPorBici);
    }


    //=================== CONSULTA PARA GRAFICA DE EL ADMIN ===================
    //esta consulta se trae la cantidad de recorridos hechos por semana en el año actual

    //es la que muestra el mes y el dia de inicia cada la semana
    public function recorridosTerminadosPorSemana(){
        $recorridos = Recorrido::raw(function($collection){
            return $collection->aggregate([
                [
                    '$match' => [
                        'acabado' => false
                    ]//Cambiar a true para que solo traiga los recorridos acabados
                ],
                [
                    '$group' => [
                        '_id' => [
                            '$isoWeek' => '$created_at'
                        ],
                        'total' => ['$sum' => 1]
                        ],
                ],
                [
                    '$sort' => ['_id' => 1]
                ],
            ]);
        });
        // dd($recorridos);

        $isoAnio = Carbon::now()->isoWeekYear;//el año en formato iso(2025)
        $data = [];
        foreach($recorridos as $recorrido){
        //de cada semana ocupo sacar el mes y el dia en el q se hizo
            
            $fechaDeLaSemana = Carbon::now()->setISODate($isoAnio, $recorrido->_id);//fecha de cada semana en el año
            $data[] = [
                'mes' => $fechaDeLaSemana->month,
                'dia' => $fechaDeLaSemana->day,
                'cantidadRecorridos' => $recorrido->total
            ];
        }//saco la fecha de cada semana en el año

        $meses = [
            0 => 'Enero',
            1 => 'Febrero',
            2 => 'Marzo',
            3 => 'Abril',
            4 => 'Mayo',
            5 => 'Junio',
            6 => 'Julio',
            7 => 'Agosto',
            8 => 'Septiembre',
            9 => 'Octubre',
            10 => 'Noviembre',
            11 => 'Diciembre'
        ];

        for($i = 0; $i < count($data); $i++){
            $data[$i]['mes'] = $meses[$data[$i]['mes'] - 1];//cambio el iso del mes por el nombre del mes
        }
        // dd($data);
        // dd($isoYear);

        // dd($recorridos);
        return response()->json($data);
    }
}
