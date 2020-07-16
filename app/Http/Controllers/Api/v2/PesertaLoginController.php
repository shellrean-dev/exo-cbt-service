<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Auth;
use App\Peserta;
use App\UjianAktif;
use Illuminate\Support\Facades\Validator;

class PesertaLoginController extends Controller
{
    /**
     * [login description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function login(Request $request)
    {

        $request->validate([
            'no_ujian'      => 'required|exists:pesertas,no_ujian',
            'password'      => 'required'
        ]);

        $peserta = Peserta::where([
            'no_ujian' => $request->no_ujian,
            'password' => $request->password
        ])->first();

        if($peserta) {
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
}
