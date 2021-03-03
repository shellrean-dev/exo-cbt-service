<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\UjianService;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\UjianAktif;
use App\SiswaUjian;
use App\HasilUjian;
use Carbon\Carbon;
use App\Banksoal;
use App\Peserta;
use App\Jadwal;
use App\Token;

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
        $token = Token::orderBy('id')->first();
        if($token) {
            $token->status = '1';
            $token->save();
            
            return SendResponse::accept();
        }
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
    public function getPesertas(Jadwal $jadwal)
    {
        $siswa = SiswaUjian::with(['peserta' => function($query) {
            $query->select('id', 'nama', 'no_ujian');
        }])
        ->select('id','jadwal_id','mulai_ujian','mulai_ujian_shadow','sisa_waktu','status_ujian','peserta_id')
        ->where(['jadwal_id' => $jadwal->id])->get();
        return SendResponse::acceptData($siswa);
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
    public function resetUjianPeserta(Jadwal $jadwal, Peserta $peserta)
    {
        $aktif = $jadwal->id;
        DB::beginTransaction();

        try {
            DB::table('siswa_ujians')->where([
                'peserta_id'        => $peserta->id,
                'jadwal_id'         => $aktif
            ])->delete();

            DB::table('jawaban_pesertas')->where([
                'peserta_id'        => $peserta->id,
                'jadwal_id'         => $aktif
            ])->delete();

            DB::table('hasil_ujians')->where([
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
    public function closePeserta($jadwal_id, $peserta_id)
    {
        try {
            $hasilUjian = DB::table('hasil_ujians')->where([
                'peserta_id'    => $peserta_id,
                'jadwal_id'     => $jadwal_id,
            ])->count();

            if($hasilUjian > 0) { 
                return SendResponse::accept();
            }

            $jawaban = DB::table('jawaban_pesertas')->where([
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id
            ])
            ->select('banksoal_id')
            ->first();

            DB::beginTransaction();
            $finished = UjianService::finishingUjian($jawaban->banksoal_id, $jadwal_id, $peserta_id);
            if(!$finished['success']) {
                DB::rollback();
                return SendResponse::badRequest($finished['message']);
            }

            DB::table('siswa_ujians')->where([
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id
            ])->update([
                'status_ujian'  => 1
            ]);

            DB::table('pesertas')->where('id', $peserta_id)->update([
                'api_token' => ''
            ]);

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

    /**
     * [getToken description]
     * @return [type] [description]
     */
    public function getToken()
    {
        $token = Token::orderBy('id')->first();

        if($token) {
            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now());
            $from = $token->updated_at->format('Y-m-d H:i:s');
            $differ = $to->diffInSeconds($from);
            if($differ > 900) {
                $token->token = strtoupper(Str::random(6));
                $token->status = '0';
                $token->save();
            }

            return SendResponse::acceptData($token);
        }
        $token = Token::create([
            'token'     => strtoupper(Str::random(6)),
            'status'    => 0,
        ]);
        return SendResponse::acceptData($token);
    }

    /**
     * Tambah waktu ujian untuk siswa
     * @param int $peserta_id
     * @param int $jadwal_id
     */
    public function addMoreTime(Request $request)
    {
        $jadwal_id = $request->jadwal_id;
        $peserta_id = $request->peserta_id;

        
        $jadwal = DB::table('jadwals')->where('id', $jadwal_id)
            ->select('id','lama')
            ->first();
        if (!$jadwal) {
            return SendResponse::badRequest('Kami tidak dapat menemukan jadwal yang diminta');
        }
        
        if (intval($request->minutes) > ($jadwal->lama/60)) {
            return SendResponse::badRequest('Penambahan waktu tidak boleh melebihi lamanya ujian');
        }

        $peserta = DB::table('pesertas')->where('id', $peserta_id)
            ->select('id','no_ujian')
            ->first();
        if (!$peserta) {
            return SendResponse::badRequest('Kami tidak dapat menemukan peserta yang diminta');
        }

        $ujian = DB::table('siswa_ujians')->where([
            'jadwal_id' => $jadwal_id,
            'peserta_id' => $peserta_id
        ])->first();

        if (!$ujian) {
            return SendResponse::badRequest('Kami tidak dapat menemukan ujian pada peserta yang diminta');
        }

        try {
            $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian_shadow);
            $curr = $start->addMinutes(intval($request->minutes));

            if ($curr > now()) {
                return SendResponse::badRequest('Anda menambah waktu ujian siswa diluar nalar, waktu yang ditambahkan akan melebihi waktu anda saat ini');
            }

            DB::table('siswa_ujians')->where('id', $ujian->id)
                ->update([
                    'mulai_ujian_shadow'    => $curr->format('H:i:s')
                ]);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Kesalahana 500. '.$e->getMessage());
        }
        return SendResponse::accept('Waktu pengerjaan siswa no ujian: '.$peserta->no_ujian. ' ditambah '.$request->minutes.' menit, informasikan kepada peserta untuk merefresh browser');
    }
}
