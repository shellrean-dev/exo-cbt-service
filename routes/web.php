<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['basicAuth'])->group(function () {
    Route::get('/system-administration-exo-cbt', 'System\SystemController@index')->name('system.exo.index');
    Route::get('/system-administration-exo-cbt/change-ip', 'System\SystemController@changeIP')->name('system.exo.change.ip');
    Route::post('/system-administration-exo-cbt/change-ip', 'System\SystemController@storeChangeIP')->name('system.exo.change.ip.store');
    Route::get('/system-administration-exo-cbt/check-update', 'System\SystemController@checkUpdate')->name('system.exo.check.update');
});

Route::view('/{any}', 'ujian')->where('any','.*');