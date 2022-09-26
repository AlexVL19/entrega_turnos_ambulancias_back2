<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormularioController;

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
Route::controller(FormularioController::class)->group(function () {
    Route::get('/getVerifications', 'getVerifications');
    Route::get('/getResponses', 'getResponses');
    Route::post('/insertIntoBitacora', 'insertIntoBitacora');
    Route::post('insertIntoMain', 'insertIntoMainBitacora');
    Route::get('/getCategories', 'getCategories');
});
