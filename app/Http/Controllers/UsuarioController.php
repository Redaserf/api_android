<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use Carbon\Carbon;
use FuncInfo;
use Hamcrest\Type\IsObject;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\UTCDateTime;

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
                'estatura' => 'numeric|between:1.10,2.20|nullable',
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
                'estatura.numeric' => 'La estatura debe ser un número.',
                'estatura.between' => 'La estatura debe estar entre 1.10m y 2.20m.',
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
                    'estatura' => $user->estatura,
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
                'peso' => $user->peso,
                'estatura' => $user->estatura
            ],
        ], 200);
    }



    //estadisticas d ecada usuario

    //consulta para traerse lo q viene siendo
    //la siguiente consulta recibira una fecha y esa fecha tiene q ser siempre un lunes
    //y a partir de esa fecha se hara la consulta para traerse los recorridos de ese lunes hasta el domingo de esa semana

    //esto hay q adapatarlo en ios para q solo mande la fecha del lunes de la semana en la q esta
    public function estadisticasDeLaSemana(Request $req)
    {

        $usuario = $req->user();
        $fechaLunes = Carbon::now()->startOfWeek(Carbon::MONDAY);//esto da la fecha del lunes de la semana actual
        $lunes = new UTcDateTime($fechaLunes->copy()->startOfDay()->timestamp * 1000);//esto da error pero si tienes descargado correctamente el pcel de mongo no deberia dar error
        $domingo = new UTcDateTime($fechaLunes->copy()->addDays(6)->endOfDay()->timestamp * 1000);


        $recorridos = Recorrido::raw(function($collection) use ($usuario, $lunes, $domingo) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'usuario._id' => $usuario->id,
                        'acabado' => true,//cambiar a true para q solo traiga los recorridos acabados
                        'created_at' => [
                            '$gte' => $lunes,
                            '$lte' => $domingo,
                        ],
                    ],
                ],
                [
                '$group' => [
                        '_id' => [
                            '$isoDayOfWeek' => [
                                '$created_at'
                            ]
                        ],//agrupa por dias
                        'distancia_recorrida' => ['$sum' => '$distancia_recorrida'], //distancia recorrida por dia
                        'calorias' => ['$sum' => '$calorias'], //calorias quemadas por dia
                        'duracion_final' => [
                            '$sum' =>[
                                '$divide' => [
                                    '$duracion_final',
                                    60//duracion por minutos
                                ]
                            ]
                        ], //duracion total por dia en minutos
                ]
                ],
                [
                    '$sort' => [
                        '_id' => 1
                    ]
                ],//ordena de lunes a domingo ej: lunes = 1, martes = 2, miercoles = 3, jueves = 4, viernes = 5, sabado = 6, domingo = 7
            ]);
        });//recorridos ya viene por dia, Lunes, Martes, Miercoles, Jueves, Viernes, Sabado, Domingo o sea q trae 7 documentos y trae la distancia, calorias, duracion por dia
        // dd($recorridos);

        $labels = [];//aqui guardamos los datos de cada dia de la semana
        $data = [
            'distancias' => [],
            'calorias' => [],
            'duraciones' => [],
        ];

        $diasSemana = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo'
        ];

        foreach($recorridos as $recorrido) {
            $dia = $recorrido->_id;
            $labels[] = [$diasSemana[$dia]];
        }//sacar el dia de cada recorrido y guardarlo en el array labels

        foreach($recorridos as $recorrido) {
            $data['distancias'][] = $recorrido->distancia_recorrida;
            $data['calorias'][] = $recorrido->calorias;
            $data['duraciones'][] = $recorrido->duracion_final;
        }//sacar la distancia, calorias y duracion de cada recorrido y guardarlo en el array data

        $estadisticasGenerales = [
            'distancias' => [
                'total' => $recorridos->sum('distancia_recorrida'),
                'promedio' => $recorridos->avg('distancia_recorrida') ?? 0,
                'maxima' => $recorridos->max('distancia_recorrida') ?? 0,
                'minima' => $recorridos->min('distancia_recorrida') ?? 0,
            ],
            'calorias' => [
                'total' => $recorridos->sum('calorias'),
                'promedio' => $recorridos->avg('calorias') ?? 0,
                'maxima' => $recorridos->max('calorias') ?? 0,
                'minima' => $recorridos->min('calorias') ?? 0,
            ],
            'duraciones' => [
                'total' => $recorridos->sum('duracion_final'),
                'promedio' => $recorridos->avg('duracion_final') ?? 0,
                'maxima' => $recorridos->max('duracion_final') ?? 0,
                'minima' => $recorridos->min('duracion_final') ?? 0,
            ],
        ];

        // dd($estadisticasGenerales);

        return response()->json([
            'message' => 'Estadísticas de la semana.',
            'generales' => $estadisticasGenerales,
            'data' => $data,
            'labels' => $labels,
        ], 200);

    }

    
    //=================== Resumen de cada usuario TOTAL () ==================
    
    public function resumenTotal(Request $req)
    {
        $usuario = $req->user();
    
        $recorridos = Recorrido::raw(function($collection) use ($usuario) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'usuario._id' => $usuario->id,
                        'acabado' => true, // Solo recorridos terminados
                    ],
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'total_recorridos' => ['$sum' => 1], // Contar la cantidad de recorridos
                        'distancia_recorrida' => ['$sum' => '$distancia_recorrida'],
                        'calorias' => ['$sum' => '$calorias'],
                        'duracion_final' => [
                            '$sum' => [
                                '$divide' => [
                                    '$duracion_final',
                                    60 // Convertir duración a minutos
                                ]
                            ]
                        ],
                    ]
                ],
            ]);
        });
    
        $recorridos = $recorridos->first();
    
        return response()->json([
            'msg' => 'Resumen total de recorridos.',
            'recorridos' => $recorridos->total_recorridos ?? 0,
            'distancia' => $recorridos->distancia_recorrida ?? 0,
            'calorias' => $recorridos->calorias ?? 0,
            'duracion' => $recorridos->duracion_final ?? 0,
        ], 200);
    }    

    
}
