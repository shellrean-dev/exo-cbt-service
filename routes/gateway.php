<?php
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api\Gateway'], function() {
    Route::group(['middleware' => 'auth:sanctum'], function() {
        Route::get('jurusans/all', 'JurusanGatewayController@allData');
        Route::get('agamas/all', 'AgamaGatewayController@allData');
        Route::get('matpels/all', 'MatpelGatewayController@allData');
        Route::get('groups/all', 'GroupGatewayController@allData');
        Route::get('banksoals/all', 'BanksoalGatewayController@allData');
        Route::get('events/all', 'EventGatewayController@allData');

        Route::get('ujians/all', 'JadwalGatewayController@allData');
        Route::get('ujians/active-status', 'JadwalGatewayController@activeStatus');

        Route::get('users/correctors', 'UserGatewayController@correctors');
    });
});
