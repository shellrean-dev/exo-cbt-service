<?php

namespace App\Http\Controllers\Api\v2;

use App\Actions\SendResponse;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Setting;
use App\Peserta;
use Illuminate\Support\Facades\DB;
use ShellreanDev\Cache\CacheHandler;
use Browser;

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
     * @param Request $request
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function login(Request $request)
    {
        $setting = Setting::where('name','ujian')->first();
        $user_agent_whitelist = Setting::where('name','user-agent-whitelist')->first();
        $userAgent = $request->header('User-Agent');

        $request->validate([
            'no_ujian'      => 'required',
            'password'      => 'required'
        ]);

        $peserta = Peserta::where([
            'no_ujian' => $request->no_ujian,
            'password' => $request->password
        ])->first();

        if($peserta) {
            if ($peserta->status == 0) {
                return SendResponse::acceptCustom(['status' => 'susspend', 'reason' => $peserta->block_reason]);
            }

            if ($user_agent_whitelist && $user_agent_whitelist->value != '*' && !str_contains($userAgent, $user_agent_whitelist->value)) {
                return SendResponse::badRequest('Anda tidak menggunakan browser yang diizinkan');
            }

            if(isset($setting->value['reset'])
                && $setting->value['reset']
                && $peserta->api_token != '') {
                    return SendResponse::acceptCustom([
                        'status'    => 'loggedin'
                    ]);
            }
            $token = $peserta->id.'|'.Str::random(64);
            $peserta->update(['api_token' => $token]);

            $peserta = $peserta->only('id','nama','no_ujian','sesi');
            $send_peserta = $peserta;
            $send_peserta['ip'] = request()->ip();
            $send_peserta['browser'] = Browser::browserName();
            $send_peserta['flatform'] = Browser::platformName();

            return SendResponse::acceptCustom([
                'status'    => 'success',
                'data'      => $send_peserta,
                'token'     => $token
            ]);
        }

        return SendResponse::acceptCustom(['status' => 'error', 'message' => 'Email/password salah']);
    }

    /**
     * @Route(path="api/v2/peserta/logout", methods={"GET"})
     *
     * Logout from system
     *
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function logout()
    {
        $user = request()->get('peserta-auth');

        $peserta = Peserta::find($user['id']);
        $peserta->api_token = '';
        $peserta->save();

        return SendResponse::acceptCustom(['status' => 'success']);
    }

    /**
     * @Route(path="api/v2/peserta-authenticated", methods={"GET"})
     *
     * Get peserta's authenticated
     *
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function authenticated()
    {
        $peserta = request()->get('peserta-auth')->only('id','nama','no_ujian','sesi', 'status', 'block_reason');
        $peserta['ip'] = request()->ip();
        $peserta['browser'] = Browser::browserName();
        $peserta['flatform'] = Browser::platformName();

        return SendResponse::acceptCustom(['data' => $peserta]);
    }

    /**
     * @Route(path="api/v2/setting", methods={"GET"})
     *
     * Get setting needed by peserta
     * s
     * @param CacheHandler $cache
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getSetting(CacheHandler $cache)
    {
        # ambil setting sekolah
        $sekolah = DB::table('settings')
            ->where('name', 'set_sekolah')
            ->first();

        # ambil setting ujian
        $ujian = DB::table('settings')
            ->where('name', 'ujian')
            ->first();

        if ($sekolah){
            $sekolah->value = json_decode($sekolah->value, true);
        }

        if ($ujian) {
            $ujian->value = json_decode($ujian->value, true);
        }

        $return = [
            'sekolah'   => [
                'logo' => $sekolah->value['logo'] ?? '',
                'nama' => $sekolah->value['nama_sekolah'] ?? ''
            ],
            'text' => [
                'welcome' => $ujian->value['text_welcome'] ?? '',
                'finish'  => $ujian->value['text_finish'] ?? ''
            ],
            'only_fullscreen' => $ujian->value['only_fullscreen'] ?? '0'
        ];
        return SendResponse::acceptData($return);
    }
}
