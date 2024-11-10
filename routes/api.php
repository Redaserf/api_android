<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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
    Route::post('v1/logout', [AuthController::class,'logout']);
        // ===[ Endpoint para la activacion de la cuenta ]===
        Route::get('activate/{id}',[AuthController::class,'activate'])->name('activation.verify');

