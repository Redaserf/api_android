<?php

namespace App\Http\Controllers;

use App\Models\Bicicleta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BicicletaController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $bicis = Bicicleta::all();

        return response()->json([
            'mensaje' => 'Todo salio bien',
            'bicis' => $bicis
        ]);
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
            'nombre' => 'required|string|max: 60',
        ], [
            'nombre.required' => 'El nombre es un campo obligatorio',
            'nombre.string' => 'El nombre debe ser de tipo string',
            'nombre.max' => 'El nombre debe ser de menos de 60 caracteres',

        ]);


        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }

        $bici = Bicicleta::create([
            'nombre' => $request->nombre,
            'usuario_id' => $request->user()->id
        ]);

        return response()->json([
            'mensaje' => 'Se creo correctamente la bici',
            'bici' => $bici
        ], 201);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bici  $bici
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //

        $bici = Bicicleta::findOrFail($id);

        if($bici){
            return response()->json([
                'mensaje' => 'Todo salio bien',
                'bici' => $bici
            ], 200);
        }else{
            return response()->json([
                'mensaje' => 'No se encontro la bici',
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bici  $bici
     * @return \Illuminate\Http\Response
     */
    public function edit(Bicicleta $bici)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bici  $bici
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $validaciones = Validator::make($request->all(), [
            'nombre' => 'required|string|max: 60'
        ], [
            'nombre.required' => 'El nombre es un campo obligatorio',
            'nombre.string' => 'El nombre debe ser de tipo string',
            'nombre.max' => 'El nombre debe ser de menos de 60 caracteres',

        ]);


        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }

        $bici = Bicicleta::findOrFail($id);
        
        if($bici){
            
            $bici->update([
                'nombre' => $request->nombre
            ]);
            $bici->save();

            return response()->json([
                'mensaje' => 'Se edito correctamente la bici',
                'bici' => $bici
            ], 200);

        }else{
            return response()->json([
                'mensaje' => 'No se encontro la bici'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bici  $bici
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        $bici = Bicicleta::findOrFail($id);

        if($bici){

            $bici->delete();

            return response()->json([
                'mensaje' => 'Se elimino correctamente la bici',
                'bici' => $bici
            ], 200);

        }else{
            return response()->json([
                'mensaje' => 'No se encontro la bici'
            ], 404);
        }
    }
}
