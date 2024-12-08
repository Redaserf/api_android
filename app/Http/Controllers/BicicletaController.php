<?php

namespace App\Http\Controllers;

use App\Models\Bicicleta;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Environment\Environment;

use function PHPSTORM_META\map;

class BicicletaController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $bicis = Bicicleta::where('usuario_id', $request->user()->id)->get();
   

        return response()->json([
            'mensaje' => 'Todo salio bien',
            'bicicletas' => $bicis->load('recorridos')
        ]);
    }

    public function imagen(Request $request, $id){

        $bici = Bicicleta::findOrFail($id);
        $imagen = Storage::disk('public')->get($bici->imagen);

        return response($imagen, 200, [
            'Content-Type' => 'image/png'
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
            'imagen' => 'required|file|image|mimes:jpg,jpeg,png',
        ], [
            'nombre.required' => 'El nombre es un campo obligatorio',
            'nombre.string' => 'El nombre debe ser de tipo string',
            'nombre.max' => 'El nombre debe ser de menos de 60 caracteres',

            'imagen.required' => 'La imagen es requerida',
            'imagen.file' => 'La imagen debe ser un archivo',
            'imagen.mimes' => 'La imagen debe ser de tipo PNG o JPG',


        ]);

        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }

        $path = Storage::disk('public')->put('images', $request->imagen);

        


        $bici = Bicicleta::create([
            'nombre' => $request->nombre,
            'imagen' => config("app_url.url") . Storage::url($path),
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
            'nombre' => 'string|max: 60',
            'imagen' => 'file|image|mimes:jpg,jpeg,png',
        ], [
            'nombre.string' => 'El nombre debe ser de tipo string',
            'nombre.max' => 'El nombre debe ser de menos de 60 caracteres',

            'imagen.file' => 'La imagen debe ser un archivo',
            'imagen.mimes' => 'La imagen debe ser de tipo PNG, JPG o JPEG',
        ]);


        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errores' => $validaciones->errors()
            ], 422);
        }
        
        $bici = Bicicleta::findOrFail($id);
        
        if($bici){
            
            $path = null;
            if($bici->imagen && $request->imagen){
                $rutaRelativa = str_replace( config("app_url.url") . "/storage/", "", $bici->imagen);

                Storage::disk('public')->delete($rutaRelativa);
                $path = Storage::disk('public')->put('images', $request->imagen);
            }
            else if($request->imagen){
                $path = Storage::disk('public')->put('images', $request->imagen);
            }
            
            $bici->nombre = $request->nombre ? $request->nombre : $bici->nombre;
            $bici->imagen = $path ? config("app_url.url") . Storage::url($path) : $bici->imagen;
            $bici->save();



            return response()->json([
                'mensaje' => 'Se edito correctamente la bici',
                'bicicleta' => $bici
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
            $rutaRelativa = str_replace( config("app_url.url") . "/storage/", "", $bici->imagen);

            Storage::disk('public')->delete($rutaRelativa);
            $bici->delete();

            return response()->json([
                'mensaje' => 'Se elimino correctamente la bici',
                'bicicleta' => $bici
            ], 200);

        }else{
            return response()->json([
                'mensaje' => 'No se encontro la bici'
            ], 404);
        }
    }
}
