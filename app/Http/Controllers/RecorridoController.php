<?php

namespace App\Http\Controllers;

use App\Events\RecorridoActivo;
use App\Models\Recorrido;
use App\Models\Rol;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RecorridoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        //recycler de los recorridos ya hechos de cada usuario
        //
        $recorridos = Recorrido::select('created_at', 'velocidad_maxima',
         'velocidad_promedio', 'temperatura',
            'calorias', 'distancia_recorrida', 
                'tiempo')
        ->where('usuario_id', $request->user()->id)
        ->with('bicicleta')
        ->get();

        return response()->json([
            'mensaje' => 'Todo salió bien',
            'recorridos' => $recorridos
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function store(Request $request)
     {
        // dd(config('database.connections.mongodb'));
         try {
             $request->validate([
                 'bicicleta_id' => 'required|exists:bicicletas,id',
             ]);
     
             $usuario_id = Auth::id();
            
            $recorridoActivo = Recorrido::raw(function ($collection) use ($usuario_id) {
                return $collection->findOne([
                    'usuario._id' => $usuario_id,
                    'acabado' => false,
                ]);
            });

            if($recorridoActivo) {
                $recorridoActivo->acabado = true;
                $recorridoActivo->save();
            }

             $recorrido = Recorrido::create([
                 'usuario' => ['_id' => $usuario_id, 'rol_id' => Auth::user()->rol_id],
                 'bicicleta_id' => $request->bicicleta_id,
                 'calorias' => 0,
                 'tiempo' => '00:00:00',
                 'velocidad' => 0,
                 'velocidad_promedio' => 0,
                 'velocidad_maxima' => 0,
                //  'suma_velocidad' => ['suma' => 0, 'cantidad' => 0],
                 'suma' => 0,
                 'cantidad' => 0,
                 'distancia_recorrida' => 0,
                 'temperatura' => 0,
                 'duracion_final' => 0,
                 'acabado' => false,
             ]);

             event(new RecorridoActivo($recorrido));
     
             return response()->json([
                 'message' => 'Recorrido creado correctamente.',
                 'recorrido_id' => $recorrido->id,
             ], 201);
         } catch (\Exception $e) {
             return response()->json([
                 'error' => 'Error al crear el recorrido',
                 'details' => $e->getMessage(),
             ], 500);
         }
     }
     


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Recorrido  $recorrido
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $recorrido = Recorrido::findOrFail($id);

        return response()->json([
            'mensaje' => 'Todo salio bien',
            'recorrido' => $recorrido
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Recorrido  $recorrido
     * @return \Illuminate\Http\Response
     */
    public function edit(Recorrido $recorrido)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Recorrido  $recorrido
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validaciones = Validator::make($request->all(), [
            'calorias' => 'numeric',
            'tiempo' => 'nullable|date_format:H:i:s',
            'velocidad' => 'numeric',
            'velocidad_promedio' => 'numeric',
            'velocidad_maxima' => 'numeric',
            // 'suma_velocidad' => 'array',
            'suma' => 'numeric',
            'cantidad' => 'numeric',
            'distancia_recorrida' => 'numeric',
            'duracion_final' => 'numeric',
            'acabado' => 'boolean'
        ], [
            'calorias.numeric' => 'El campo calorias debe ser de tipo double',
            'tiempo.date_format' => 'El campo tiempo debe ser con el formato HH:MM:SS',
            'velocidad_promedio.numeric' => 'La velocidad promedio debe ser de tipo double',
            'velocidad_maxima.numeric' => 'La velocidad máxima debe ser de tipo double',
            'distancia_recorrida.numeric' => 'La distancia recorrida debe ser de tipo double',
            'duracion_final.numeric' => 'La duración final debe ser de tipo double',
            'acabado.boolean' => 'El campo acabado debe ser de tipo boolean'
        ]);

        if ($validaciones->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }

        $recorrido = Recorrido::findOrFail($id);

        if ($recorrido) {
            $recorrido->calorias = $request->calorias ?? $recorrido->calorias;
            $recorrido->tiempo = $request->tiempo ?? $recorrido->tiempo;
            $recorrido->velocidad = $request->velocidad ?? $recorrido->velocidad;
            $recorrido->velocidad_promedio = $request->velocidad_promedio ?? $recorrido->velocidad_promedio;
            $recorrido->velocidad_maxima = $request->velocidad_maxima ?? $recorrido->velocidad_maxima;
            $recorrido->distancia_recorrida = $request->distancia_recorrida ?? $recorrido->distancia_recorrida;
            $recorrido->duracion_final = $request->duracion_final ?? $recorrido->duracion_final;
            $recorrido->acabado = $request->acabado ?? $recorrido->acabado;

            $recorrido->save();

            if (
                $recorrido->acabado &&
                $recorrido->tiempo &&
                preg_match('/^\d{2}:\d{2}:\d{2}$/', $recorrido->tiempo)
            ) {
                [$horas, $minutos, $segundos] = explode(':', $recorrido->tiempo);
                $tiempoSegundos = ($horas * 3600) + ($minutos * 60) + $segundos;
                $recorrido->duracion_final = $tiempoSegundos;
                $recorrido->save();
            }

            // if($recorrido->acabado){
            // }
            event(new RecorridoActivo($recorrido));

            return response()->json([
                'mensaje' => 'El recorrido se editó correctamente',
                'recorrido' => $recorrido
            ], 200);
        }

        return response()->json([
            'mensaje' => 'No se encontró el recorrido'
        ], 404);
    }
    //no he probado si  funciona con mongoDB

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Recorrido  $recorrido
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        $recorrido = Recorrido::findOrFail($id);

        if($recorrido){


            $recorrido->delete();

            return response()->json([
                'mensaje' => 'Se elimino correctamente el recorrido',
                'recorrido' => $recorrido
            ]);

        }else{
            return response()->json([
                'mensaje' => 'No se encontro el recorrido'
            ], 404);
        }
    }//no he probado si  funciona con mongoDB

    // =======================================================================================

    //=================== [ De aqui para abajo ya todo funciona con mongoDB @hugo]=============================

    public function obtenerDatosWeb(Request $req)
    {
        $usuario = $req->user();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $recorrido = Recorrido::where('usuario._id', $usuario->id)
            ->where('acabado', false)
            ->first();

        if (!$recorrido) {
            return response()->json(['message' => 'No hay recorrido activo'], 404);
        }

        return response()->json([
            'mensaje' => 'Recorrido activo encontrado',
            'recorrido' => $recorrido
        ], 200);
    }

    // para iOS
    public function recorridosUsuario()
    {
        $usuario = Auth::user();
    
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
    
        $recorridos = $usuario->recorridos(function ($query) {
            $query->with('bicicleta')->orderBy('created_at', 'desc');
        })
        ->map(function ($recorrido) {
            return [
                'id' => $recorrido->id,
                'bicicleta_nombre' => $recorrido->bicicleta()->nombre ?? 'Sin nombre',
                'calorias' => $recorrido->calorias,
                'tiempo' => $recorrido->tiempo,
                'velocidad_promedio' => $recorrido->velocidad_promedio,
                'velocidad_maxima' => $recorrido->velocidad_maxima,
                'distancia_recorrida' => $recorrido->distancia_recorrida,
                'temperatura' => $recorrido->temperatura,
                'created_at' => $recorrido->created_at->toDateTimeString(),
            ];
        });        
    
        return response()->json([
            'message' => 'Recorridos obtenidos con éxito',
            'recorridos' => $recorridos,
        ], 200);
    }
    
    // para WEB
    public function recorridosUsuarioPaginado(Request $request)
    {
        $usuario = Auth::user();
    
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
    
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 8);
    
        $recorridos = $usuario->recorridos(function ($query) {
            $query->with('bicicleta');
        });
    
        $total = $recorridos->count();
        $recorridosPaginados = $recorridos->forPage($page, $perPage);
    
        $formatted = $recorridosPaginados->map(function ($recorrido) {
            return [
                'id' => $recorrido->id,
                'bicicleta_nombre' => optional($recorrido->bicicleta())->nombre ?? 'Sin nombre',
                'calorias' => $recorrido->calorias,
                'tiempo' => $recorrido->tiempo,
                'velocidad_promedio' => $recorrido->velocidad_promedio,
                'velocidad_maxima' => $recorrido->velocidad_maxima,
                'distancia_recorrida' => $recorrido->distancia_recorrida,
                'temperatura' => $recorrido->temperatura,
                'created_at' => $recorrido->created_at->toDateTimeString(),
            ];
        });
    
        return response()->json([
            'data' => [
                'data' => $formatted->values(),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page
            ]
        ], 200);
    }

    public function recorridosPorSemana()
    {
        $usuario = Auth::user();
    
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
    
        $hoy = Carbon::now()->endOfDay();
        $haceUnaSemana = Carbon::now()->subDays(7)->startOfDay();
    
        $recorridos = $usuario->recorridos(function ($recorrido) use ($haceUnaSemana, $hoy) {
            $recorrido->whereBetween('created_at', [$haceUnaSemana, $hoy])
                ->bicicleta();
            })
            ->map(function ($recorrido) {
                return [
                    'bicicleta_nombre' => $recorrido->bicicleta()->nombre ?? 'Sin nombre',
                    'calorias' => $recorrido->calorias,
                    'tiempo' => $recorrido->tiempo,
                    'velocidad_promedio' => $recorrido->velocidad_promedio,
                    'velocidad_maxima' => $recorrido->velocidad_maxima,
                    'distancia_recorrida' => $recorrido->distancia_recorrida,
                    'created_at' => $recorrido->created_at->toDateTimeString(),
                ];
            });
        
    
        return response()->json([
            'message' => 'Recorridos de la última semana obtenidos con éxito',
            'recorridos' => $recorridos,
        ], 200);
    }
    
    public function recorridosPorMes()
    {
        $usuario = Auth::user();
    
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
    
        $hoy = Carbon::now()->endOfDay();
        $haceUnMes = Carbon::now()->subDays(30)->startOfDay();
    
        $recorridos = $usuario->recorridos(function ($recorrido) use ($haceUnMes, $hoy) {
            $recorrido->whereBetween('created_at', [$haceUnMes, $hoy])
                ->bicicleta();
            })
            ->map(function ($recorrido) {
                return [
                    'bicicleta_nombre' => $recorrido->bicicleta()->nombre ?? 'Sin nombre',
                    'calorias' => $recorrido->calorias,
                    'tiempo' => $recorrido->tiempo,
                    'velocidad_promedio' => $recorrido->velocidad_promedio,
                    'velocidad_maxima' => $recorrido->velocidad_maxima,
                    'distancia_recorrida' => $recorrido->distancia_recorrida,
                    'created_at' => $recorrido->created_at->toDateTimeString(),
                ];
            });
    
        return response()->json([
            'message' => 'Recorridos del último mes obtenidos con éxito',
            'recorridos' => $recorridos,
        ], 200);
    }    

}
