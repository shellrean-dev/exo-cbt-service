<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Setting;
use App\Peserta;
use Illuminate\Support\Facades\DB;
use ShellreanDev\Cache\CacheHandler;

/**
 * PesertaLoginControler
 * @author shellrean <wandinak17@gmail.com>
 */
class PesertaLoginController extends Controller
{
    /**
     * @Route(path="api/v2/logedin", methods={"POST"})
     * 
     * Login to system
     * 
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function login(Request $request)
    {
        $setting = Setting::where('name','ujian')->first();

        $request->validate([
            'no_ujian'      => 'required',
            'password'      => 'required'
        ]);

        $peserta = Peserta::where([
            'no_ujian' => $request->no_ujian,
            'password' => $request->password
        ])->first();

        if($peserta) {
            if(isset($setting->value['reset'])
                && $setting->value['reset']
                && $peserta->api_token != '') {
                    return response()->json([
                        'status'    => 'loggedin'
                    ], 200);
            }
            $token = Str::random(128);
            $peserta->update(['api_token' => $token]);
            return response()
            ->json([
                'status'    => 'success',
                'data'      => $peserta->only('nama','no_ujian','sesi'),
                'token'     => $token
            ],200);
        }

        return response()->json(['status' => 'error']);
    }

    /**
     * @Route(path="api/v2/peserta/logout", methods={"GET"})
     * 
     * Logout from system
     * 
     * @return Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function logout()
    {
        $user = request()->get('peserta-auth');

        $peserta = Peserta::find($user['id']);
        $peserta->api_token = '';
        $peserta->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * @Route(path="api/v2/peserta-authenticated", methods={"GET"})
     * 
     * Get peserta's authenticated
     * 
     * @return Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function authenticated()
    {
        $peserta = request()->get('peserta-auth')->only('nama','no_ujian','sesi');
        return ['data' => $peserta];
    }

    /**
     * @Route(path="api/v2/setting", methods={"GET"})
     * 
     * Get setting needed by peserta
     * s
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSetting(CacheHandler $cache)
    {
        // ambil setting sekolah
        $key = md5(sprintf('setting:data:set_sekolah'));
        if ($cache->isCached($key)) {
            $sekolah = $cache->getItem($key);
        } else {
            $sekolah = DB::table('settings')
                ->where('name', 'set_sekolah')
                ->first();

            $cache->cache($key, $sekolah);
        }

        // ambil setting ujian
        $key = md5(sprintf('setting:data:ujian'));
        if ($cache->isCached($key)) {
            $ujian = $cache->getItem($key);
        } else {
            $ujian = DB::table('settings')
                ->where('name', 'ujian')
                ->first();

            $cache->cache($key, $ujian);
        }

        if ($sekolah){
            $sekolah->value = json_decode($sekolah->value, true);
        }

        if ($ujian) {
            $ujian->value = json_decode($ujian->value, true);
        }

        $return = [
            'sekolah'   => [
                'logo' => isset($sekolah->value['logo']) ? $sekolah->value['logo'] : '',
                'nama' => isset($sekolah->value['nama_sekolah']) ? $sekolah->value['nama_sekolah'] : ''
            ],
            'text' => [
                'welcome' => isset($ujian->value['text_welcome']) ? $ujian->value['text_welcome'] : '',
                'finish'  => isset($ujian->value['text_finish']) ? $ujian->value['text_finish'] : ''
            ]
        ];
        return response()->json(['data' => $return]);
    }
}
