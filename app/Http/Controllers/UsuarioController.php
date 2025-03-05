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



    //estadisticas d ecada usuario

    //consulta para traerse lo q viene siendo
    //la siguiente consulta recibira una fecha y esa fecha tiene q ser siempre un lunes
    //y a partir de esa fecha se hara la consulta para traerse los recorridos de esa semana
    public function estadisticasDeLaSemana(Request $req)
    {

        $validaciones = Validator::make($req->all(), [
            'fecha' => [
                'required',
                'date_format:Y-m-d',
                function($attribute, $value, $fail) {
                    // Verifica si la fecha es lunes
                    if (!Carbon::parse($value)->isMonday()) {
                        $fail('La fecha debe ser un lunes.');
                    }
                }
            ]
        ]);

        if ($validaciones->fails()) {
            return response()->json([
                'message' => 'Errores en los datos enviados.',
                'errors' => $validaciones->errors(),
            ], 422);
        }

        $usuario = $req->user();
        $fechaLunes = Carbon::parse($req->fecha);
        $fechas = [
            'lunes'   => new UTCDateTime($fechaLunes->copy()->startOfDay()->timestamp * 1000),//esto da error pero funciona cuando descargas correctamente el pcel de mongodb
            'martes'    => $fechaLunes->copy()->addDay()->startOfDay()->toIso8601String(),
            'miercoles' => $fechaLunes->copy()->addDays(2)->startOfDay()->toIso8601String(),
            'jueves'    => $fechaLunes->copy()->addDays(3)->startOfDay()->toIso8601String(),
            'viernes'   => $fechaLunes->copy()->addDays(4)->startOfDay()->toIso8601String(),
            'sabado'    => $fechaLunes->copy()->addDays(5)->startOfDay()->toIso8601String(),
            'domingo'   => new UTCDateTime($fechaLunes->copy()->addDays(6)->endOfDay()->timestamp * 1000),//esto da error pero funciona cuando descargas correctamente el pcel de mongodb
        ];


        $recorridos = Recorrido::raw(function($collection) use ($usuario, $fechas) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'usuario._id' => $usuario->id,
                        'acabado' => false,//cambiar a true para q solo traiga los recorridos acabados
                        'created_at' => [
                            '$gte' => $fechas['lunes'],
                            '$lte' => $fechas['domingo'],
                        ],
                    ],//aqui falta ponerle q este acabado
                ],
                [
                   '$group' => [
                        '_id' => [
                            '$dateToString' => [
                                'format' => "%Y-%m-%d",
                                'date' => '$created_at'
                            ]
                        ],//agrupa por dias
                        'distancia_recorrida' => ['$sum' => '$distancia_recorrida'], //distancia recorrida por dia
                        'calorias' => ['$sum' => '$calorias'], //calorias quemadas por dia
                        'duracion_final' => ['$sum' => '$duracion_final'], //duracion total por dia
                   ]
                ],
                [
                    '$addFields' => [
                      'diaSemana' => [ '$isoDayOfWeek' => [ '$toDate' => '$_id' ] ]
                    ]
                ],
                [
                    '$sort' => [
                        'diaSemana' => 1
                    ]
                ],//ordena de lunes a domingo ej: lunes = 1, martes = 2, miercoles = 3, jueves = 4, viernes = 5, sabado = 6, domingo = 7
            ]);
        });//recorridos ya viene por dia, Lunes, Martes, Miercoles, Jueves, Viernes, Sabado, Domingo o sea q trae 7 documentos y trae la distancia, calorias, duracion por dia

        // dd($recorridos);

        $estadisticasGenerales = [
            'distancia' => [
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
            'duracion' => [
                'total' => $recorridos->sum('duracion_final'),
                'promedio' => $recorridos->avg('duracion_final') ?? 0,
                'maxima' => $recorridos->max('duracion_final') ?? 0,
                'minima' => $recorridos->min('duracion_final') ?? 0,
            ],
        ];

        // dd($estadisticasGenerales);

       
        return response()->json([
            'message' => 'Estadísticas de la semana.',
            'data' => [
                'generales' => $estadisticasGenerales,
                'porDiaSemana' => $recorridos,
            ],
        ], 200);

    }

    
    
}
