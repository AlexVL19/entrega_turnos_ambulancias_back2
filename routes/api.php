<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormularioController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VistaController;

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

/* Se utiliza el middleware de Sanctum para proteger las rutas */
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(FormularioController::class)->group(function () {
        Route::get('/getVerifications', 'getVerifications');
        Route::get('/getResponses', 'getResponses');
        Route::post('/insertIntoBitacora', 'insertIntoBitacora');
        Route::post('insertIntoMain', 'insertIntoMainBitacora');
        Route::get('/getCategories', 'getCategories');
    });

    Route::controller(LoginController::class)->group(function () {
        Route::post('/logout', 'logout');
    });

    Route::controller(VistaController::class)->group(function () {
        Route::post('/getTurnData', 'getTurnData');
        Route::put('/addComments', 'addCommentsToBitacora');
    });
});

Route::controller(LoginController::class)->group(function () {
    Route::post('/login', 'authenticate');
});
