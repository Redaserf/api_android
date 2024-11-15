<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BicicletaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecorridoController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



    Route::prefix("v1/")->group(function(){

    // ==========[ CRUD's ]==========

        // ===[ Users ]===
        Route::post('register', [AuthController::class,'register']);
        Route::post('login', [AuthController::class,'login']);
        Route::post('logout', [AuthController::class,'logout']);
        
        // ===[ Activar cuenta y reenviar email ]===
        Route::post('reenviar', [AuthController::class,'reenviar']);
        Route::get('activate/{id}',[AuthController::class,'activate'])->name('activation.verify');

        // ===[ Middleware |Tiene que estar logueado| ]===
        Route::middleware(['auth:sanctum'])->group(function () {
            
            // ===[ Bicicletas ]===
            Route::post('bicicleta', [BicicletaController::class, 'store']); // Crear
            Route::put('bicicleta/{id}', [BicicletaController::class, 'update'])->where('id', '[0-9]+'); // Editar
            Route::get('bicicletas', [BicicletaController::class, 'index']); // Traer todas las bicis
            Route::get('bicicleta/{id}', [BicicletaController::class, 'show'])->where('id', '[0-9]+'); // Traer una bici
            Route::delete('bicicleta/{id}', [BicicletaController::class, 'destroy'])->where('id', '[0-9]+');//eliminar una bici
        
            // ===[ Recorridos ]===
            Route::post('recorrido', [RecorridoController::class, 'store']); // Crear
            Route::put('recorrido/{id}', [RecorridoController::class, 'update'])->where('id', '[0-9]+'); // Editar
            Route::get('recorridos', [RecorridoController::class, 'index']); // Traer todos los recorridos
            Route::get('recorrido/{id}', [RecorridoController::class, 'show'])->where('id', '[0-9]+'); // Traer un recorrido
            Route::delete('recorrido/{id}', [RecorridoController::class, 'destroy'])->where('id', '[0-9]+'); // Eliminar un recorrido
            
        });

    });
        

