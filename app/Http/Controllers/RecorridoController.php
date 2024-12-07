<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
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
         try {
             $request->validate([
                 'bicicleta_id' => 'required|exists:bicicletas,id',
             ]);
     
             $usuario_id = Auth::id();
     
             $recorrido = Recorrido::create([
                 'usuario_id' => $usuario_id,
                 'bicicleta_id' => $request->bicicleta_id,
                 'calorias' => 0,
                 'tiempo' => 0,
                 'velocidad_promedio' => 0,
                 'velocidad_maxima' => 0,
                 'distancia_recorrida' => 0,
                 'temperatura' => 0,
             ]);
     
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
        //
        $validaciones = Validator::make($request->all(), [
            'calorias' => 'numeric',
            'tiempo' => 'date_format:H:i:s',
            'velocidad_promedio' => 'numeric',
            'velocidad_maxima' => 'numeric',
            'distancia_recorrida' => 'numeric',
            
        ], [
            'calorias.numeric' => 'El campo calorias debe ser de tipo double',

            'tiempo.date_format' => 'El campo tiempo debe ser con el formato HH::MM::SS',

            'velocidad_promedio' => 'La velocidad promedio debe ser de tipo double',
            
            'velocidad_maxima' => 'La velocidad maxima debe ser de tipo double',

            'distancia_recorrida' => 'La distancia recorrida debe ser de tipo double',
            

        ]);

        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }

        $recorrido = Recorrido::findOrFail($id);

        if($recorrido){
            $recorrido->calorias = $request->calorias ?? $recorrido->calorias;
            $recorrido->tiempo = $request->tiempo ?? $recorrido->tiempo;
            $recorrido->velocidad_promedio = $request->velocidad_promedio ?? $recorrido->velocidad_promedio;
            $recorrido->velocidad_maxima = $request->velocidad_maxima ?? $recorrido->velocidad_maxima;
            $recorrido->distancia_recorrida = $request->distancia_recorrida ?? $recorrido->distancia_recorrida;
    
            $recorrido->save();


            return response()->json([
                'mensaje' => 'El recorrido se edito correctamente',
                'recorrido' => $recorrido
            ], 200);

        }else{
            return response()->json([
                'mensaje' => 'No se encontro el recorrido'
            ], 404);
        }

    }

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
    }

    // =======================================================================================

    public function recorridosUsuario()
    {
        $usuario = Auth::user();
    
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
    
        $recorridos = $usuario->recorridos()
            ->with('bicicleta')
            ->get()
            ->map(function ($recorrido) {
                return [
                    'bicicleta_nombre' => $recorrido->bicicleta->nombre ?? 'Sin nombre',
                    'calorias' => $recorrido->calorias,
                    'tiempo' => $recorrido->tiempo,
                    'velocidad_promedio' => $recorrido->velocidad_promedio,
                    'velocidad_maxima' => $recorrido->velocidad_maxima,
                    'distancia_recorrida' => $recorrido->distancia_recorrida,
                    'created_at' => $recorrido->created_at->toDateTimeString(),
                ];
            });
    
        return response()->json([
            'message' => 'Recorridos obtenidos con éxito',
            'recorridos' => $recorridos,
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
    
        $recorridos = $usuario->recorridos()
            ->whereBetween('created_at', [$haceUnaSemana, $hoy])
            ->with('bicicleta')
            ->get()
            ->map(function ($recorrido) {
                return [
                    'bicicleta_nombre' => $recorrido->bicicleta->nombre ?? 'Sin nombre',
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
    
        $recorridos = $usuario->recorridos()
            ->whereBetween('created_at', [$haceUnMes, $hoy])
            ->with('bicicleta')
            ->get()
            ->map(function ($recorrido) {
                return [
                    'bicicleta_nombre' => $recorrido->bicicleta->nombre ?? 'Sin nombre',
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
