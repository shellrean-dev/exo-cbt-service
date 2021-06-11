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
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\Jadwal\JadwalService;
use ShellreanDev\Services\Ujian\UjianService as DevUjianService;

class UjianAktifController extends Controller
{

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
     * [getPesertas description]
     * @return [type] [description]
     */
    public function getPesertas($jadwal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->count();
        if ($jadwal < 1) {
            return SendResponse::badRequest('kesalahan, jadwal tidak ditemukan');
        }
        // $siswa = SiswaUjian::with(['peserta' => function($query) {
        //     $query->select('id', 'nama', 'no_ujian');
        // }])
        // ->select('id','jadwal_id','mulai_ujian','mulai_ujian_shadow','sisa_waktu','status_ujian','peserta_id')
        // ->where(['jadwal_id' => $jadwal->id])->get();
        $siswa = DB::table('siswa_ujians')
            ->join('pesertas', 'siswa_ujians.peserta_id', '=', 'pesertas.id')
            ->select('siswa_ujians.id', 'siswa_ujians.jadwal_id','siswa_ujians.mulai_ujian','siswa_ujians.mulai_ujian_shadow','siswa_ujians.sisa_waktu','siswa_ujians.status_ujian','siswa_ujians.peserta_id','pesertas.nama','pesertas.no_ujian')
            ->where(['siswa_ujians.jadwal_id' => $jadwal_id])
            ->get();
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
        $peserta->save();

        return SendResponse::accept();
    }   

    /**
     * [resetUjianPeserta description]
     * @param  Request $request [description]
     * @param  Peserta $peserta [description]
     * @return [type]           [description]
     */
    public function resetUjianPeserta(Jadwal $jadwal, Peserta $peserta, CacheHandler $cache)
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

            // remove completed jadwal cache
            $key = md5(sprintf('jadwal:data:peserta:%s:ujian:complete', $peserta->id));
            $cache->cache($key, '', 0);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * Multiple reset peserta ujian
     * 
     * @param App\Jadwal $jadwal
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return App\Actions\SendResponse
     */
    public function multiResetUjianPeserta(Jadwal $jadwal, CacheHandler $cache)
    {
        $aktif = $jadwal->id;
        DB::beginTransaction();

        $pesertas = explode(',', request()->q);

        try {
            DB::table('siswa_ujians')
                ->where('jadwal_id', $aktif)
                ->whereIn('peserta_id', $pesertas)
                ->delete();

            DB::table('jawaban_pesertas')
                ->where('jadwal_id', $aktif)
                ->whereIn('peserta_id', $pesertas)
                ->delete();

            DB::table('hasil_ujians')
                ->where('jadwal_id', $aktif)
                ->whereIn('peserta_id', $pesertas)
                ->delete();

            DB::table('pesertas')
                ->whereIn('id', $pesertas)
                ->update([
                    'api_token' => ''
                ]);

            foreach ($pesertas as $peserta) {
                // remove completed jadwal cache
                $key = md5(sprintf('jadwal:data:peserta:%s:ujian:complete', $peserta));
                $cache->cache($key, '', 0);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * Paksa selesaikan ujian peserta
     * 
     * @param string $jadwal_id
     * @param string $peserta_id
     * @param ShellreanDev\Services\UjianService $ujianService
     * @return App\Actions\SendResponse
     */
    public function closePeserta($jadwal_id, $peserta_id, DevUjianService $ujianService, CacheHandler $cache)
    {
        try {
            $hasilUjian = DB::table('hasil_ujians')->where([
                'peserta_id'    => $peserta_id,
                'jadwal_id'     => $jadwal_id,
            ])->count();

            if($hasilUjian > 0) { 
                return SendResponse::accept('hasil ujian peserta sudah digenerate');
            }

            $jawaban = DB::table('jawaban_pesertas')->where([
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id
            ])
            ->select('banksoal_id')
            ->first();

            DB::beginTransaction();

            $ujianService->finishing($jawaban->banksoal_id, $jadwal_id, $peserta_id);

            DB::table('siswa_ujians')->where([
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id
            ])->update([
                'status_ujian'  => 1
            ]);

            DB::table('pesertas')->where('id', $peserta_id)->update([
                'api_token' => ''
            ]);
                
            $key = md5(sprintf('jadwal:data:peserta:%s:ujian:complete', $peserta_id));
            $cache->cache($key, '', 0);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError($e->getMessage());
        }
        return SendResponse::accept('ujian peserta berhasil diforce close');
    }

    /**
     * Multi paksa selesaikan ujian peserta
     * 
     * @param string $jadwal_id
     * @param string $peserta_id
     * @param ShellreanDev\Services\UjianService $ujianService
     * @return App\Actions\SendResponse
     */
    public function multiClosePeserta($jadwal_id, DevUjianService $ujianService, CacheHandler $cache)
    {
        try {
            DB::beginTransaction();
            $pesertas = explode(',', request()->q);
            if (count($pesertas) < 1) {
                return SendResponse::badRequest('tidak ada peserta yang dipilih');
            }

            $hasilUjian = DB::table('hasil_ujians')
                ->whereIn('peserta_id', $pesertas)
                ->where('jadwal_id', $jadwal_id)
                ->select('peserta_id')
                ->get();
            $finishedPeserta = $hasilUjian->pluck('peserta_id')->toArray();
            
            $unfinishPeserta = [];
            foreach ($pesertas as $peserta) {
                if (!in_array($peserta, $finishedPeserta)) {
                    $unfinishPeserta[] = $peserta;
                }
            }

            if (count($unfinishPeserta) < 1) {
                return SendResponse::badRequest('siswa yang dipilih telah menyelesaikan ujiannya');
            }

            foreach ($unfinishPeserta as $peserta) {
                $jawaban = DB::table('jawaban_pesertas')->where([
                    'jadwal_id'     => $jadwal_id, 
                    'peserta_id'    => $peserta
                ])
                ->select('banksoal_id')
                ->first();

                $ujianService->finishing($jawaban->banksoal_id, $jadwal_id, $peserta);
            }

            DB::table('siswa_ujians')
                ->whereIn('peserta_id', $unfinishPeserta)
                ->where('jadwal_id', $jadwal_id)
                ->update([
                    'status_ujian' => 1
                ]);

            DB::table('pesertas')
                ->whereIn('id', $unfinishPeserta)
                ->update([
                    'api_token' => ''
                ]);

            foreach ($unfinishPeserta as $peserta) {
                // remove completed jadwal cache
                $key = md5(sprintf('jadwal:data:peserta:%s:ujian:complete', $peserta));
                $cache->cache($key, '', 0);
            }
                
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError($e->getMessage());
        }
        return SendResponse::accept('ujian peserta yang dipilih berhasil diforce close');
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
