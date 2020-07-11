<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\UjianAktif;
use App\Peserta;

class UjianAktifController extends Controller
{
    /**
     * [index description]
     * @return [type] [description]
     */
    public function index()
    {
        $ujian = UjianAktif::with(['jadwal'])->first();
        
        if($ujian) {
            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now());
            $from = $ujian['updated_at']->format('Y-m-d H:i:s');
            $differ = $to->diffInSeconds($from);

            if($differ > 900) {
                $ujian->token = strtoupper(Str::random(6));
                $ujian->status_token = 0;
                $ujian->save();
            }  
        }
        else {
            $ujian = [];
        }
        return SendResponse::acceptData($ujian);
    }

    /**
     * [sesi description]
     * @return [type] [description]
     */
    public function sesi()
    {
        $sesi = Peserta::groupBy('sesi')->select('sesi')->get();
        return SendResponse::acceptData($sesi);
    }

    /**
     * [storeStatus description]
     * @return [type] [description]
     */
    public function storeStatus()
    {
        $ujian = UjianAktif::first();
        $siswa = SiswaUjian::where(['status_ujian' => 3])->first();
        if($siswa) {
            return SendResponse::badRequest('Masih ada siswa yang mengerjakan');
        }
        if($ujian) {
            $ujian->ujian_id = $request->jadwal;
            $ujian->kelompok = $request->kelompok;
            $ujian->save();
        } else {
            UjianAktif::create([
                'kelompok'  => $request->kelompok,
                'ujian_id'    => $request->jadwal,
                'token'     => strtoupper(Str::random(6)),
                'status_token' => 0
            ]);
        }
        Peserta::where('api_token','!=','')->update(['api_token' => '']);
        return SendResponse::accept();
    }

    /**
     * [releaseToken description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function releaseToken(Request $request) 
    {
        $ujian = UjianAktif::first();
        if($ujian) {
            $ujian->token = $request->token;
            $ujian->status_token = 1;
            $ujian->save();

        } else {
            UjianAktif::create([
                'token'  => $request->token,
            ]);
        }
        return SendResponse::accept();
    }

    /**
     * [changeToken description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function changeToken(Request $request)
    {
        $jadwal = UjianAktif::first();
        if($jadwal) {
            $jadwal->token = strtoupper(Str::random(6));
            $jadwal->status_token = 0;
            $jadwal->save();

            return SendResponse::acceptData($jadwal);
        }
    }
}
