<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\UjianAktif;
use App\SiswaUjian;
use App\HasilUjian;
use App\Peserta;
use App\Jadwal;

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
    public function storeStatus(Request $request)
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

    /**
     * [getPesertas description]
     * @return [type] [description]
     */
    public function getPesertas()
    {
        $ujian = UjianAktif::first();
        if($ujian) {
            $siswa = SiswaUjian::with('peserta')->where(['jadwal_id' => $ujian->ujian_id])->get();
            return SendResponse::acceptData($siswa);
        }
    }

    /**
     * [resetPeserta description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function resetPeserta(Request $request, Peserta $peserta)
    {
        $peserta->api_token = '';
        $siswa->save();

        return SendResponse::accept();
    }   

    /**
     * [resetUjianPeserta description]
     * @param  Request $request [description]
     * @param  Peserta $peserta [description]
     * @return [type]           [description]
     */
    public function resetUjianPeserta(Request $request, Peserta $peserta)
    {
        $aktif = UjianAktif::first()->ujian_id;
        DB::beginTransaction();

        try {
            SiswaUjian::where([
                'peserta_id'        => $peserta->id,
                'jadwal_id'         => $aktif
            ])->delete();

            JawabanPeserta::where([
                'peserta_id'        => $peserta->id,
                'jadwal_id'         => $aktif
            ])->delete();
        
            $peserta->api_token = '';
            $peserta->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * [closePeserta description]
     * @param  Request $request [description]
     * @param  Peserta $peserta [description]
     * @return [type]           [description]
     */
    public function closePeserta(Request $request, Peserta $peserta)
    {
        $aktif = UjianAktif::first();

        DB::beginTransaction();

        try {
            $ujian = SiswaUjian::where([
                'jadwal_id'     => $aktif->ujian_id, 
                'peserta_id'    => $peserta->id
            ])->first();

            $ujian->status_ujian = 1;
            $ujian->save();

            $banksoal = JawabanPeserta::where([
                'jadwal_id'     => $aktif->ujian_id, 
                'peserta_id'    => $peserta->id
            ])->first();

            $salah = JawabanPeserta::where([
                'iscorrect'     => 0,
                'jadwal_id'     => $aktif->ujian_id, 
                'peserta_id'    => $peserta->id,
                'esay'    => null
            ])->get()->count();

            $benar = JawabanPeserta::where([
                'iscorrect'     => 1,
                'jadwal_id'     => $aktif->ujian_id, 
                'peserta_id'    => $peserta->id
            ])->get()->count();
            
            $jml = JawabanPeserta::where([
                'jadwal_id'     => $aktif->ujian_id, 
                'peserta_id'    => $peserta->id
            ])->get()->count();

            $hasil = ($benar/$jml)*100;

            HasilUjian::create([
                'banksoal_id'     => $banksoal->id,
                'peserta_id'      => $peserta->id,
                'jadwal_id'       => $aktif->ujian_id,
                'jumlah_salah'    => $salah,
                'jumlah_benar'    => $benar,
                'tidak_diisi'     => 0,
                'hasil'           => $hasil,
                'point_esay'      => 0.0,
                'jawaban_peserta' => ''
            ]);

            $peserta->api_token = '';
            $peserta->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * [changeSesi description]
     * @param  Request $request [description]
     * @param  Jadwal  $jadwal  [description]
     * @return [type]           [description]
     */
    public function changeSesi(Request $request, Jadwal $jadwal )
    {
        $request->validate([
            'sesi'      => 'required'
        ]);

        $jadwal->sesi = $request->sesi;
        $jadwal->save();

        return SendResponse::accept();
    }
}
