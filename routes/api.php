<?php

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

/**
 * @version 1
 * api response for v1
 */
Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function() {
    Route::post('login', 'AuthController@login');
    Route::get('user-authenticated', 'UserController@getUserLogin');
    
    Route::get('agamas', 'AgamaController@index');
    
    Route::get('jurusans/all', 'JurusanController@allData');
    Route::apiResource('jurusans', 'JurusanController');

    Route::post('pesertas/upload', 'PesertaController@import');
    Route::apiResource('pesertas', 'PesertaController');
});