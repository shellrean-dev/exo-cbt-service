<?php

use Illuminate\Support\Facades\Route;

/**
 * @version 2 request
 * @handle for peserta only
 *
 * @author shellrean <wandinak17@gmail.com>
 */
Route::namespace('Api\v2')->group(function() {

    Route::post('logedin','PesertaLoginController@login');
    Route::get('setting', 'PesertaLoginController@getSetting');

    Route::group(['middleware' => 'peserta'], function() {
        Route::get('peserta-authenticated', 'PesertaLoginController@authenticated');
        Route::get('peserta/logout','PesertaLoginController@logout');
        Route::get('jadwals/peserta', 'JadwalController@getJadwalPeserta');
        Route::get('ujians/uncomplete','UjianAktifController@uncompleteUjian');
        Route::get('ujians/peserta', 'UjianAktifController@getUjianPesertaAktif');
        Route::post('ujians/start', 'UjianAktifController@startUjian');
        Route::post('ujians/start/time', 'UjianAktifController@startUjianTime');
        Route::get('ujians/filled', 'UjianAktifController@getJawabanPeserta');
        Route::post('ujian/hasils', 'UjianAktifController@getHasilUjian');
        Route::post('ujian','UjianController@store');
        Route::post('ujian/ragu-ragu', 'UjianController@setRagu');
        Route::get('ujian/selesai', 'UjianController@selesai');
        Route::post('ujians/leave-counter', 'UjianAktifController@leaveCounter');
        Route::post('ujians/block-me-please', 'UjianAktifController@blockMePlease');
    });
});
