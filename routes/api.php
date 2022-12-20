<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VistaController;
use App\Http\Controllers\ListaTurnosController;
use App\Http\Controllers\NovedadesController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/* Rutas agrupadas que pertenecen a un controlador en común. */

/* Se utiliza el middleware de Sanctum para proteger las siguientes rutas */
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(FormularioController::class)->group(function () {
        Route::get('/getVerifications', 'getVerifications');
        Route::get('/getResponses', 'getResponses');
        Route::post('/insertIntoBitacora', 'insertIntoBitacora');
        Route::post('insertIntoMain', 'insertIntoMainBitacora');
        Route::get('/getCategories', 'getCategories');
        Route::get('getAmbulanceData/{movil}', 'getAmbulanceData');
        Route::post('/getCargas', 'getEquiposConCarga');
        Route::put('/setCargasFinales', 'setCargasFinales');
        Route::get('/getConfigs', 'getConfigs');
        Route::get('/getTiposProductos', 'getTiposProductosAseo');
        Route::get('/getProductosAseo', 'getProductosAseo');
        Route::post('/enviarFormularioAseo', 'enviarFormularioAseo');
        Route::post('/enviarFormularioTemp', 'enviarFormularioTemperatura');
        Route::post('/validarJornada', 'validarJornada');
        Route::post('/insertNovedades', 'insertarNovedad');
    });

    Route::controller(LoginController::class)->group(function () {
        Route::post('/logout', 'logout');
    });

    Route::controller(VistaController::class)->group(function () {
        Route::post('/getTurnData', 'getTurnData');
        Route::put('/addComments', 'addCommentsToBitacora');
    });

    Route::controller(ListaTurnosController::class)->group(function () {
        Route::get('/getTurnosEntregados', 'getTurnosEntregados');
        Route::get('/getAuxiliares', 'getAuxiliares');
        Route::get('/getConductores', 'getConductores');
        Route::get('/getMoviles', 'getMoviles');
        Route::post('/getNovedades', 'getNovedades');
        Route::get('/getResponsesForNovedades', 'getResponsesForNovedades');
        Route::post('/getFormulario', 'getFormulario');
        Route::post('/consultarCargas', 'consultarCargas');
        Route::post('/consultarAseo', 'consultarAseo');
        Route::post('/consultarTemperaturas', 'consultarTemperaturas');
        Route::post('/filtrarRegistros', 'filtroRegistros');
    });

    Route::controller(NovedadesController::class)->group(function () {
        Route::get('/getNovedades', 'getNovedades');
        Route::get('/getVerifsNovedades', 'getVerificacionesNovedades');
        Route::post('/getTurno', 'findTurno');
        Route::post('/enviarDatosRevision', 'enviarDatosCambio');
        Route::get('/getCategoriasNovedad', 'getCategoriasNovedad');
    });
});

Route::controller(LoginController::class)->group(function () {
    Route::post('/login', 'authenticate');
});

Route::controller(ListaTurnosController::class)->group(function() {
    Route::post('/exportarDatos', 'exportarDatos');
});
