<?php

use Illuminate\Support\Facades\Route;

/**
 * @version 3 request
 * @author shellrean <wandinak17@gmail.com>
 */
Route::namespace('Api\v3')->group(function() {
    Route::middleware('auth:api')->group(function() {
        Route::get('agamas', 'AgamaController@index');

        Route::get('jurusans-all', 'JurusanController@all');
        Route::post('jurusans-delete', 'JurusanController@deletes');
        Route::get('jurusans', 'JurusanController@index');
        Route::post('jurusans', 'JurusanController@store');
        Route::get('jurusans/{id}', 'JurusanController@show');
        Route::put('jurusans/{id}', 'JurusanController@update');
        Route::delete('jurusans/{id}', 'JurusanController@destroy');
    });
});