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

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'no_ujian'      => 'required|exists:pesertas,no_ujian',
            'password'      => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()],422);
        }

        $peserta = Peserta::where([
            'no_ujian' => $request->no_ujian,
            'password' => $request->password
        ])->first();
        $aktif = UjianAktif::first();
        
        if(!$aktif) {
            return response()->json(['status' => 'Ujian has not been set']);
        }

        if($peserta) {
            if($peserta->api_token != '') {
                return response()->json(['status' => 'loggedin']);
            }
            if($aktif->kelompok != $peserta->sesi) {
                return response()->json(['status' => 'non-sesi']);
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

    public function logout() 
    {
        $user = request()->get('peserta-auth');

        $peserta = Peserta::find($user['id']);
        $peserta->api_token = '';
        $peserta->save();

        return response()->json(['status' => 'success']);
    }

    public function authenticated()
    {
        $peserta = request()->get('peserta-auth')->only('nama','no_ujian','sesi');
        return ['data' => $peserta];
    }
}
