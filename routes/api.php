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

Route::get('ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa/excel', 'Api\v1\UjianController@getCapaianSiswaExcel');

Route::group(['prefix' => 'v1', 'namespace' => 'Api\v1'], function() {
    Route::post('login', 'AuthController@login');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('user-authenticated', 'UserController@getUserLogin');
        Route::get('user-lists', 'UserController@userLists');
        Route::post('user/change-password', 'UserController@changePassword');
        
        Route::get('agamas', 'AgamaController@index');
        
        Route::get('jurusans/all', 'JurusanController@allData');
        Route::apiResource('jurusans', 'JurusanController');

        Route::get('pesertas/login', 'PesertaController@getPesertaLogin');
        Route::delete('pesertas/{peserta}/login', 'PesertaController@resetPesertaLogin');
        Route::post('pesertas/upload', 'PesertaController@import');
        Route::apiResource('pesertas', 'PesertaController');

        Route::get('matpels/all', 'MatpelController@allData');
        Route::post('matpels/upload', 'MatpelController@import');
        Route::apiResource('matpels', 'MatpelController');

        Route::get('banksoals/{banksoal}/analys', 'BanksoalController@getAnalys');
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
        Route::get('ujians/peserta', 'UjianAktifController@getPesertas');
        Route::get('ujians/peserta/{peserta}/reset', 'UjianAktifController@resetUjianPeserta');
        Route::get('ujians/peserta/{peserta}/close', 'UjianAktifController@closePeserta');

        Route::get('ujians/esay/exists', 'UjianController@getExistEsay');
        Route::post('ujians/esay/input', 'UjianController@storeNilaiEsay');
        Route::get('ujians/esay/{banksoal}/koreksi', 'UjianController@getExistEsayByBanksoal');
        Route::get('ujians/{jadwal}/result', 'UjianController@getResult');
        Route::get('ujians/{jadwal}/result/banksoal', 'UjianController@getBanksoalByJadwal');
        Route::get('ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa', 'UjianController@getCapaianSiswa');
        Route::get('ujians/{jadwal}/banksoal/{banksoal}/capaian-siswa/excel', 'UjianController@getCapaianSiswaExcel');
        Route::get('ujians/all', 'UjianController@allData');
        Route::get('ujians/active-status', 'UjianController@getActive');
        Route::post('ujians/set-status', 'UjianController@setStatus');
        Route::apiResource('ujians', 'UjianController');

        Route::get('events/all', 'EventController@allData');
        Route::apiResource('events', 'EventController');

        Route::get('settings/sekolah', 'SettingController@getSettingSekolah');
        Route::post('settings/sekolah', 'SettingController@storeSettingSekolah');
        Route::post('settings/sekolah/logo', 'SettingController@changeLogoSekolah');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes Fo peserta
|--------------------------------------------------------------------------
|
*/
Route::group(['prefix' => 'v2', 'namespace' => 'Api\v2'], function() {

    Route::post('logedin','PesertaLoginController@login');

    Route::group(['middleware' => 'peserta'], function() {
        Route::get('peserta-authenticated', 'PesertaLoginController@authenticated');

        Route::get('/peserta/logout','PesertaLoginController@logout');
        
        Route::get('/jadwal/aktif', 'UjianController@getUjianAktif');

        Route::get('/ujian/{id}','UjianController@getsoal');
        Route::post('/ujian/setter','UjianController@getsoal');
        Route::post('/ujian','UjianController@store');
        Route::get('/ujian/jawaban/{id}', 'UjianController@getJawabanPeserta');
        Route::post('/ujian/filled', 'UjianController@filled');
        Route::post('/ujian/sisa-waktu', 'UjianController@sisaWaktu');
        Route::post('/ujian/ujian-siswa-det', 'UjianController@detUjian');
        Route::post('/ujian/ragu-ragu', 'UjianController@setRagu');
        Route::post('/ujian/selesai', 'UjianController@selesai');
        Route::post('/ujian/cektoken','UjianController@cekToken');

        Route::post('/ujian/mulai-peserta', 'UjianController@mulaiPeserta');
    });
});
