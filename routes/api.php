<?php

use App\Http\Controllers\AdafruitController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArduinoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BicicletaController;
use App\Http\Controllers\CalculosController;
use App\Http\Controllers\PruebaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecorridoController;
use App\Http\Controllers\SSEController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VelocidadController;

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

Route::middleware('auth:sanctum')->get('v1/user', function (Request $request) {
    return $request->user();
});


// ================= [IGNORAR RUTAS COMENTADAS]==================

//usuario con mas distancia recorrida
// Route::get('v1/admin/usuario/mayor/distancia', [AdminController::class, 'usuarioConMasKilometrosRecorridos'])
// ->where('id', '[0-9]+');

//recorrido con mas distancia
// Route::get('v1/admin/recorrido/mayor/distancia', [AdminController::class, 'recorridoConMasDistancia']);


//distancia recorrida por cada usuario
// Route::get('v1/admin/distancia/usuario', [AdminController::class, 'distanciaPorUsuario']);

// Route::get('bicicleta/{id}', [BicicletaController::class, 'show'])->where('id', '[0-9]+'); // Traer una bici



    Route::prefix("v1/")->group(function(){

        Route::get('recorrido/activo', [SSEController::class, 'recorridoActivo']);

    // ==========[ CRUD's ]==========

        // ===[ Users ]===
        Route::post('register', [AuthController::class,'register']);
        Route::post('login', [AuthController::class,'login']);

        // ===[ Activar cuenta y reenviar email ]===
        Route::post('reenviar', [AuthController::class,'reenviar']);
        Route::post('verify-code', [AuthController::class, 'verificarCodigo']);
        // Route::get('activate/{id}',[AuthController::class,'activate'])->name('activation.verify');

        // ===[ Middleware |Tiene que estar logueado| ]===
        Route::middleware(['auth:sanctum'])->group(function () {

            // ===[ Auth ]===
            Route::post('logout', [AuthController::class,'logout']);

            // ===[ Usuarios ]===
            Route::put('usuario', [UsuarioController::class, 'actualizar']);
            Route::get('usuario', [UsuarioController::class, 'show']);

            // ===[ Adafruit ]===
            Route::post('adafruit', [AdafruitController::class, 'obtenerDatos']);

            // ===[ Bicicletas ]===
            Route::post('bicicleta', [BicicletaController::class, 'store']); // Crear
            Route::put('bicicleta/{id}', [BicicletaController::class, 'update'])->where('id', '[0-9]+'); // Editar

            Route::get('bicicleta', [BicicletaController::class, 'index']); // Traer todas las bicis iOS
            Route::get('bicicleta/paginado', [BicicletaController::class, 'indexPaginado']); // Traer todas las bicis WEB

            Route::get('bicicleta/{id}', [BicicletaController::class, 'show'])->where('id', '[0-9]+'); // Traer una bici
            Route::delete('bicicleta/{id}', [BicicletaController::class, 'destroy'])->where('id', '[0-9]+');//eliminar una bici

            // ===[ Recorridos ]===
            Route::post('recorrido', [RecorridoController::class, 'store']); // Crear
            Route::put('recorrido/{id}', [RecorridoController::class, 'update']); // Editar

            Route::get('recorridos', [RecorridoController::class, 'recorridosUsuario']); // Traer todos los recorridos (por usuario) iOS
            Route::get('recorridos/paginado', [RecorridoController::class, 'recorridosUsuarioPaginado']); // Traer todos los recorridos (por usuario) WEB

            // Route::get('recorrido', [RecorridoController::class, 'index']); // Traer todos los recorridos, este es el de hugo
            Route::get('recorrido/{id}', [RecorridoController::class, 'show'])->where('id', '[0-9]+'); // Traer un recorrido
            Route::delete('recorrido/{id}', [RecorridoController::class, 'destroy']); // Eliminar un recorrido
            Route::get('recorridos/semana', [RecorridoController::class, 'recorridosPorSemana']);
            Route::get('recorridos/mes', [RecorridoController::class, 'recorridosPorMes']);

            // ===[ Velocidades ]===
            Route::post('velocidades', [VelocidadController::class, 'eliminarVelocidades']);

            Route::post('encender/luz', [ArduinoController::class, 'encenderMatriz']);

            // =======================[ Estadisticas Usuario ]=============================
            // estadisticas del usuario logeado
            Route::get('semana/estadisticas', [UsuarioController::class, 'estadisticasDeLaSemana']);

            //resumen total de calorias, distancia y tiempo recorrido del usuario logeado
            Route::get('resumen/usuario', [UsuarioController::class, 'resumenTotal']);

            // =======================[ Obtener datos de recorrido actual ]=============================
            Route::post('datos', [CalculosController::class, 'obtenerDatos']);
            Route::get('recorrido-activo', [RecorridoController::class, 'obtenerDatosWeb']); // para la web

            Route::middleware(['auth.admin'])->group(function () {

                //todos los usuarios
                Route::get('admin/usuarios', [AdminController::class, 'todosLosUsuarios']);


                //usuario con sus bicicletas
                Route::get('admin/show/usuario/{id}', [AdminController::class, 'showUsuarioConBicicleta'])->where('id', '[0-9]+');//

                //======================== ESTADISTICAS ========================
                            //=========== Admin ===========
                //recorridos terminados por semana //esta es para la grafica de admin line chart
                Route::get('admin/recorridos/semana', [AdminController::class, 'recorridosTerminadosPorSemana']);

                //Crud usuarios

                Route::delete('usuario/{id}', [AdminController::class, 'eliminarUsuario'])->where('id', '[0-9]+');
                Route::put('usuario/{id}', [AdminController::class, 'editarUsuario'])->where('id', '[0-9]+');
                Route::get('usuario/{id}', [AdminController::class, 'usuario'])->where('id', '[0-9]+');


                //bicicletas con usuario
                Route::get('admin/bicicletas', [AdminController::class, 'bicicletasConUsuario']);
            });

        });

    });

    // =======================[ Recibir datos de la raspberry ]=============================
    Route::post('v1/sensores', [CalculosController::class, 'calcularDatosGuardarRecorridoEnMongo']);

    Route::post('v1/prueba/actualizar',[PruebaController::class,'simulacionRecorrido']);
    Route::get('v1/prueba/conexion',[PruebaController::class,'pruebaDeConexion']);
    Route::post("json/raspberry",[PruebaController::class, 'jsonRaspberry']);
    Route::post("/debug-headers",[PruebaController::class, 'debug']);
    // == [ Re-envio de contra - DLC] ==

    Route::post('v1/password/email', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('v1/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');