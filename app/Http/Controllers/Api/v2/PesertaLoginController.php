<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Setting;
use App\Peserta;

class PesertaLoginController extends Controller
{
    /**
     * [login description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function login(Request $request)
    {
        $setting = Setting::where('name','ujian')->first();

        $request->validate([
            'no_ujian'      => 'required|exists:pesertas,no_ujian',
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
                'data'      => $peserta,
                'token'     => $token
            ],200);
        }       

        return response()->json(['status' => 'error']); 
    }

    /**
     * [logout description]
     * @return [type] [description]
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
     * [authenticated description]
     * @return [type] [description]
     */
    public function authenticated()
    {
        $peserta = request()->get('peserta-auth')->only('nama','no_ujian','sesi');
        return ['data' => $peserta];
    }

    public function getSetting()
    {
        $sekolah = Setting::where('name','set_sekolah')->first();
        $ujian = Setting::where('name','ujian')->first();
        $return = [
            'sekolah'   => [
                'logo' => isset($sekolah->value) ? $sekolah->value['logo'] : '',
                'nama' => isset($sekolah->value) ? $sekolah->value['nama_sekolah'] : ''
            ],
            'text' => [
                'welcome' => isset($ujian->value) ? $ujian->value['text_welcome'] : '',
                'finish'  => isset($ujian->value) ? $ujian->value['text_finish'] : ''
            ]
        ];
        return response()->json(['data' => $return]);
    }
}
