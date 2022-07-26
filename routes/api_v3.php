<?php

use Illuminate\Support\Facades\Route;

/**
 * @version 3 request
 * @author shellrean <wandinak17@gmail.com>
 */
Route::namespace('Api\v3')->group(function() {
    Route::get('feature-info', 'AppInfoController@info');

    Route::middleware('auth:sanctum')->group(function() {
        Route::get('agamas', 'AgamaController@index');

        Route::get('jurusans-all', 'JurusanController@all');
        Route::post('jurusans-delete', 'JurusanController@deletes');
        Route::get('jurusans', 'JurusanController@index');
        Route::post('jurusans', 'JurusanController@store');
        Route::get('jurusans/{id}', 'JurusanController@show');
        Route::put('jurusans/{id}', 'JurusanController@update');
        Route::delete('jurusans/{id}', 'JurusanController@destroy');

        Route::get('matpels-all', 'MatpelController@all');
        Route::post('matpels-delete', 'MatpelController@deletes');
        Route::post('matpels-import', 'MatpelController@import');
        Route::get('matpels', 'MatpelController@index');
        Route::post('matpels', 'MatpelController@store');
        Route::get('matpels/{id}','MatpelController@show');
        Route::put('matpels/{id}', 'MatpelController@update');

        Route::post('users-import', 'UserController@import');
        Route::post('users-delete', 'UserController@deletes');
        Route::get('users', 'UserController@index');
        Route::post('users', 'UserController@store');
        Route::get('users/{id}', 'UserController@show');
        Route::put('users/{id}' ,'UserController@update');
        Route::delete('users/{id}', 'UserController@destroy');

        Route::get('banksoal-adaptif', 'BanksoalAdaptifController@index'); // DRUNKEY
        Route::post('banksoal-adaptif', 'BanksoalAdaptifController@store'); // DRUNKEY
        Route::get('banksoal-adaptif/{banksoal_id}', 'BanksoalAdaptifController@show'); // DRUNKEY
        Route::put('banksoal-adaptif/{banksoal_id}', 'BanksoalAdaptifController@update'); // DRUNKEY
        Route::delete('banksoal-adaptif/{banksoal_id}', 'BanksoalAdaptifController@destroy'); // DRUNKEY
    });
});