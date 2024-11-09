<?php

namespace App\Http\Controllers;

use App\Models\Recorrido;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RecorridoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $recorridos = Recorrido::all();

        return response()->json([
            'mensaje' => 'Todo salio bien',
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
        //
        $validaciones = Validator::make($request->all(), [
            'calorias' => 'numeric',
            'tiempo' => 'date_format:H:i:s',
            'velocidad_promedio' => 'numeric',
            'velocidad_maxima' => 'numeric',
            'distancia_recorrida' => 'numeric',
            'bicicleta_id' => ['required', Rule::exists('bicicletas', 'id')->where(function($bici) use($request){
                //Que la bici exista en las bicis del usuario
                $bici->where('usuario_id', $request->user()->id);
            })]
        ], [
            'calorias.numeric' => 'El campo calorias debe ser de tipo double',

            'tiempo.date_format' => 'El campo tiempo debe ser con el formato HH::MM::SS',

            'velocidad_promedio' => 'La velocidad promedio debe ser de tipo double',
            
            'velocidad_maxima' => 'La velocidad maxima debe ser de tipo double',

            'distancia_recorrida' => 'La distancia recorrida debe ser de tipo double',

            'bicicleta_id.required' => 'El id de la bicicleta es obligatorio',
            'bicicleta_id.exists' => 'Esta bicicleta no le pertenece al usuario o no existe',
            
            

        ]);

        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }

        $recorrido = Recorrido::create([
            'calorias' => $request->calorias,
            'tiempo' => $request->tiempo,
            'velocidad_promedio' => $request->velocidad_promedio,
            'velocidad_maxima' => $request->velocidad_maxima,
            'distancia_recorrida' => $request->distancia_recorrida,
            'usuario_id' => $request->user()->id,
            'bicicleta_id' => $request->bicicleta_id
        ]);

        return response()->json([
            'mensaje' => 'Se creo correctamente el recorrido',
            'recorrido' => $recorrido
        ], 201);


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
}
