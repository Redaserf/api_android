<?php

namespace App\Http\Controllers;

use App\Models\Bicicleta;
use App\Models\Recorrido;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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

    // para iOS
    public function index(Request $request)
    {
        $bicis = Bicicleta::where('usuario_id', $request->user()->id)
                    ->select('id', 'nombre')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'mensaje' => 'Todo salio bien',
            'data' => $bicis
        ]);
    }

     // para WEB
    public function indexPaginado(Request $request)
    {
        $perPage = $request->get('per_page', 5);
    
        $bicis = Bicicleta::where('usuario_id', $request->user()->id)
            ->select('id', 'nombre')
            ->paginate($perPage);
    
        return response()->json([
            'mensaje' => 'Todo salió bien',
            'data' => $bicis
        ]);
    }

    // public function imagen(Request $request, $id){

    //     $bici = Bicicleta::findOrFail($id);
    //     $imagen = Storage::disk('public')->get($bici->imagen);

    //     return response($imagen, 200, [
    //         'Content-Type' => 'image/png'
    //     ]);

    // }

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
        Log::info($request->imagen);
    
        $validaciones = Validator::make($request->all(), [
            'nombre' => 'required|string|max:60',
            // 'imagen' => 'required|file|image|mimes:jpg,jpeg,png',
        ], [
            'nombre.required' => 'El nombre es un campo obligatorio',
            'nombre.string' => 'El nombre debe ser de tipo string',
            'nombre.max' => 'El nombre debe ser de menos de 60 caracteres',
            // 'imagen.required' => 'La imagen es requerida',
            // 'imagen.file' => 'La imagen debe ser un archivo',
            // 'imagen.mimes' => 'La imagen debe ser de tipo PNG o JPG',
        ]);
    
        if ($validaciones->fails()) {
            return response()->json([
                'mensaje' => 'Error en la validación de los datos',
                'errors' => $validaciones->errors()
            ], 422);
        }
    
        // Guardar la imagen en el almacenamiento público
        // $path = Storage::disk('public')->put('images', $request->imagen);
    
        // Crear la bici en la base de datos
        $bici = Bicicleta::create([
            'nombre' => $request->nombre,
            // 'imagen' => config("app_url.url") . Storage::url($path),
            'usuario_id' => $request->user()->id
        ]);
    
        $apiKey = config('adafruit_token.key');
        $username = 'Aldebaran0987Integradora';
    
        try {
            Http::withHeaders([
                'X-AIO-Key' => $apiKey,
            ])->post("https://io.adafruit.com/api/v2/{$username}/groups", [
                'name' => $bici->nombre,
                'description' => "Grupo asociado a la bici {$bici->nombre}",
            ]);
        } catch (\Exception $e) {
            Log::error("Error al crear el grupo en Adafruit IO: {$e->getMessage()}");
        }
    
        return response()->json([
            'mensaje' => 'Se creó correctamente la bici',
            'bicicleta' => $bici
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
                'data' => $bici
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
            'nombre' => 'required|string|max: 60',
            // 'imagen' => 'file|image|mimes:jpg,jpeg,png',
        ], [
            'nombre.required' => 'El nombre es un campo obligatorio',
            'nombre.string' => 'El nombre debe ser de tipo string',
            'nombre.max' => 'El nombre debe ser de menos de 60 caracteres',

            // 'imagen.file' => 'La imagen debe ser un archivo',
            // 'imagen.mimes' => 'La imagen debe ser de tipo PNG, JPG o JPEG',
        ]);


        if($validaciones->fails()){
            return response()->json([
                'mensaje' => 'Error en la validacion de los datos',
                'errors' => $validaciones->errors()
            ], 422);
        }
        
        $bici = Bicicleta::findOrFail($id);
        
        if($bici){
            
            // $path = null;
            // if($bici->imagen && $request->imagen){
            //     $rutaRelativa = str_replace( config("app_url.url") . "/storage/", "", $bici->imagen);

            //     Storage::disk('public')->delete($rutaRelativa);
            //     $path = Storage::disk('public')->put('images', $request->imagen);
            // }
            // else if($request->imagen){
            //     $path = Storage::disk('public')->put('images', $request->imagen);
            // }
            
            $bici->nombre = $request->nombre ? $request->nombre : $bici->nombre;
            // $bici->imagen = $path ? config("app_url.url") . Storage::url($path) : $bici->imagen;
            $bici->save();



            return response()->json([
                'mensaje' => 'Se editó correctamente la bici',
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
        $bici = Bicicleta::findOrFail($id);
    
        if ($bici) {
            Recorrido::where('bicicleta_id', $bici->id)->delete();
    
            $bici->delete();
    
            return response()->json([
                'mensaje' => 'Se eliminó correctamente la bici y sus recorridos',
                'data' => $bici
            ], 200);
        } else {
            return response()->json([
                'mensaje' => 'No se encontró la bici'
            ], 404);
        }
    }
}
