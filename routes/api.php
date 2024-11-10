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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// ==========[ CRUD's ]==========

    // ===[ Users ]===
    Route::post('v1/register', [AuthController::class,'register']);
    Route::post('v1/login', [AuthController::class,'login']);

    // ===[ Bicicletas ]===
    Route::middleware(['auth:sanctum'])->group(function () {
        
        Route::post('v1/bicicleta', [BicicletaController::class, 'store']);//crear
        Route::put('v1/bicicleta/{id}', [BicicletaController::class, 'update']);//editar
        Route::get('v1/bicicletas', [BicicletaController::class, 'index']);//traer todas las bicis
        Route::get('v1/bicicleta/{id}', [BicicletaController::class, 'show']);//traer una bici
        Route::delete('v1/bicicleta/{id}', [BicicletaController::class, 'destroy']);//eliminar una bici
    
        // ===[ recorridos ]===
        Route::post('v1/recorrido', [RecorridoController::class, 'store']);//crear
        Route::put('v1/recorrido/{id}', [RecorridoController::class, 'update']);//editar
        Route::get('v1/recorridos', [RecorridoController::class, 'index']);//traer todas las bicis
        Route::get('v1/recorrido/{id}', [RecorridoController::class, 'show']);//traer una bici
        Route::delete('v1/recorrido/{id}', [RecorridoController::class, 'destroy']);//eliminar una bici
        
    });
        
    Route::post('v1/logout', [AuthController::class,'logout']);
        // ===[ Endpoint para la activacion de la cuenta ]===
        Route::get('activate/{id}',[AuthController::class,'activate'])->name('activation.verify');

