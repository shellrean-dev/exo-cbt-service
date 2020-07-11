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

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('user-authenticated', 'UserController@getUserLogin');
        Route::get('user-lists', 'UserController@userLists');
        
        Route::get('agamas', 'AgamaController@index');
        
        Route::get('jurusans/all', 'JurusanController@allData');
        Route::apiResource('jurusans', 'JurusanController');

        Route::post('pesertas/upload', 'PesertaController@import');
        Route::apiResource('pesertas', 'PesertaController');

        Route::get('matpels/all', 'MatpelController@allData');
        Route::apiResource('matpels', 'MatpelController');

        Route::get('banksoals/all', 'BanksoalController@allData');
        Route::apiResource('banksoals', 'BanksoalController');

        Route::get('soals/{soal}', 'SoalController@show');
        Route::post('soals', 'SoalController@store');
        Route::post('soals/paste', 'SoalController@storePaste');
        Route::post('soals/{soal}/edit', 'SoalController@update');
        Route::delete('soals/{soal}', 'SoalController@destroy');
        Route::get('soals/banksoal/{banksoal}', 'SoalController@getSoalByBanksoal');
        Route::get('soals/banksoal/{banksoal}/all','SoalController@getSoalByBanksoalAll');
        Route::get('soals/banksoal/{banksoal}/analys', 'SoalController@getSoalByBanksoalAnalys');

        Route::apiResource('directory', 'DirectoryController');
        Route::post('directory/filemedia', 'DirectoryController@storeFilemedia');
        Route::post('file/upload', 'DirectoryController@uploadFile');
        Route::delete('directory/filemedia/{filemedia}', 'DirectoryController@deleteFilemedia');
        Route::post('upload/file-audio', 'DirectoryController@uploadAudio');
        Route::get('directory/banksoal/{filemedia}', 'DirectoryController@getDirectoryBanksoal');

        Route::get('ujians/active', 'UjianAktifController@index');
        Route::get('ujians/sesi', 'UjianAktifController@sesi');
        Route::post('ujians/status', 'UjianAktifController@storeStatus');
        Route::post('ujians/token-release', 'UjianAktifController@releaseToken');
        Route::post('ujians/token-change', 'UjianAktifController@changeToken');

        Route::get('ujians/all', 'UjianController@allData');
        Route::get('ujians/active-status', 'UjianController@getActive');
        Route::post('ujians/set-status', 'UjianController@setStatus');
        Route::apiResource('ujians', 'UjianController');


        Route::get('events/all', 'EventController@allData');
        Route::apiResource('events', 'EventController');
    });
});