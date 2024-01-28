<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\UjianConstant;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Peserta;
use App\Jadwal;
use App\SiswaUjian;
use App\Token;

use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\Ujian\UjianService as DevUjianService;

/**
 * UjianAktifController
 * @author shellrean <wandinak17@gmail.com>
 */
class UjianAktifController extends Controller
{

    /**
     * @Route(path="api/v1/ujians/sesi", methods={"GET"})
     *
     * @return App\Actions\SendResponse
     */
    public function sesi()
    {
        $sesi = Peserta::groupBy('sesi')->select('sesi')->get();
        return SendResponse::acceptData($sesi);
    }

    /**
     * @Route(path="api/v1/ujians/token-release", methods={"POST"})
     *
     * @return App\Actions\SendResponse
     */
    public function releaseToken()
    {
        $token = Token::orderBy('id')->first();
        if($token) {
            $token->status = '1';
            $token->save();

            return SendResponse::accept();
        }
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal_id}/peserta", methods={"GET"})
     *
     * @return App\Actions\SendResponse
     */
    public function getPesertas($jadwal_id)
    {
        $jadwal = DB::table('jadwals')
            ->where('id', $jadwal_id)
            ->count();
        if ($jadwal < 1) {
            return SendResponse::badRequest('kesalahan, jadwal tidak ditemukan');
        }

        $siswa = DB::table('siswa_ujians')
            ->join('pesertas', 'siswa_ujians.peserta_id', '=', 'pesertas.id')
            ->select(
                'siswa_ujians.id',
                'siswa_ujians.jadwal_id',
                'siswa_ujians.mulai_ujian',
                'siswa_ujians.mulai_ujian_shadow',
                'siswa_ujians.selesai_ujian',
                'siswa_ujians.sisa_waktu',
                'siswa_ujians.status_ujian',
                'siswa_ujians.peserta_id',
                'pesertas.nama',
                'pesertas.no_ujian'
            )
            ->where(['siswa_ujians.jadwal_id' => $jadwal_id])
            ->get();
        return SendResponse::acceptData($siswa);
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/peserta/{peserta}/reset", methods={"GET"})
     *
     * @param App\Jadwal $jadwal
     * @param App\Peserta $peserta
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return App\Actions\SendResponses
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
     * @Route(path="api/v1/ujians/peserta-ujian/{id}/delete", methods={"GET"})
     *
     */
    public function deleteUjianPeserta($id_ujian)
    {
        DB::beginTransaction();

        try {
            $siswa_ujian = DB::table('siswa_ujians')->where('id', $id_ujian)->first();
            if(!$siswa_ujian) {
                return SendResponse::badRequest('siswa ujian not found');
            }

            DB::table('pesertas')
            ->where('id', $siswa_ujian->peserta_id)
            ->update([
                'api_token' => ''
            ]);

            DB::table('siswa_ujians')->where('id', $id_ujian)->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/multi-reset", methods={"GET"})
     *
     * Multiple reset peserta ujian
     *
     * @param App\Jadwal $jadwal
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return App\Actions\SendResponse
     */
    public function multiResetUjianPeserta(Jadwal $jadwal)
    {
        $aktif = $jadwal->id;
        DB::beginTransaction();

        if(request()->q == '') {
            return SendResponse::badRequest('anda belum memilih peserta ujian');
        }

        $pesertas = explode(',', request()->q);

        if (count($pesertas) < 1) {
            return SendResponse::badRequest('pilih peserta yang akan direset');
        }

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

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::badRequest($e->getMessage());
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/peserta/{peserta}/close", methods={"GET"})
     *
     * Paksa selesaikan ujian peserta
     *
     * @param string $jadwal_id
     * @param string $peserta_id
     * @param DevUjianService $ujianService
     * @param CacheHandler $cache
     * @return Response
     */
    public function closePeserta($jadwal_id, $peserta_id, DevUjianService $ujianService, CacheHandler $cache)
    {
        try {
            $hasilUjian = DB::table('hasil_ujians')->where([
                'peserta_id'    => $peserta_id,
                'jadwal_id'     => $jadwal_id,
            ])->count();

            if($hasilUjian > 0) {
                DB::table('siswa_ujians')->where([
                    'jadwal_id'     => $jadwal_id,
                    'peserta_id'    => $peserta_id
                ])->update([
                    'status_ujian'  => UjianConstant::STATUS_FINISHED,
                    'selesai_ujian' => now()->format('H:i:s')
                ]);
                return SendResponse::accept('hasil ujian peserta sudah digenerate');
            }

            $jawaban = DB::table('jawaban_pesertas')->where([
                'jadwal_id'     => $jadwal_id,
                'peserta_id'    => $peserta_id
            ])
            ->select('banksoal_id')
            ->first();

            DB::beginTransaction();

            $ujian = DB::table('siswa_ujians')->where([
                'jadwal_id'     => $jadwal_id,
                'peserta_id'    => $peserta_id
            ])->first();

            $ujianService->finishing($jawaban->banksoal_id, $jadwal_id, $peserta_id, $ujian->id);

            DB::table('siswa_ujians')->where([
                'jadwal_id'     => $jadwal_id,
                'peserta_id'    => $peserta_id
            ])->update([
                'status_ujian'  => UjianConstant::STATUS_FINISHED,
                'selesai_ujian' => now()->format('H:i:s')
            ]);

            DB::table('pesertas')->where('id', $peserta_id)->update([
                'api_token' => ''
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError($e->getMessage());
        }
        return SendResponse::accept('ujian peserta berhasil diforce close');
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/multi-close", methods={"GET"})
     *
     * Multi paksa selesaikan ujian peserta
     *
     * @param string $jadwal_id
     * @param DevUjianService $ujianService
     * @param CacheHandler $cache
     * @return Response
     */
    public function multiClosePeserta($jadwal_id, DevUjianService $ujianService, CacheHandler $cache)
    {
        try {
            DB::beginTransaction();
            if(request()->q == '') {
                return SendResponse::badRequest('anda belum memilih peserta ujian');
            }

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
                return SendResponse::badRequest('peserta yang dipilih telah menyelesaikan ujiannya');
            }

            foreach ($unfinishPeserta as $peserta) {
                $jawaban = DB::table('jawaban_pesertas')->where([
                    'jadwal_id'     => $jadwal_id,
                    'peserta_id'    => $peserta
                ])
                ->select('banksoal_id')
                ->first();

                $ujian = DB::table('siswa_ujians')->where([
                    'jadwal_id'     => $jadwal_id,
                    'peserta_id'    => $peserta
                ])->first();

                $ujianService->finishing($jawaban->banksoal_id, $jadwal_id, $peserta, $ujian->id);
            }

            DB::table('siswa_ujians')
                ->whereIn('peserta_id', $unfinishPeserta)
                ->where('jadwal_id', $jadwal_id)
                ->update([
                    'status_ujian' => UjianConstant::STATUS_FINISHED,
                    'selesai_ujian' => now()->format('H:i:s')
                ]);

            DB::table('pesertas')
                ->whereIn('id', $unfinishPeserta)
                ->update([
                    'api_token' => ''
                ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError($e->getMessage());
        }
        return SendResponse::accept('ujian peserta yang dipilih berhasil diforce close');
    }

    /**
     * @Route(path="api/v1/ujians/{jadwal}/sesi-change", methods={"POST"})
     *
     * @param  Illuminate\Http\Request $request
     * @param  App\Jadwal  $jadwal
     * @return App\Actions\SendResponse
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
     * @Route(path="api/v1/ujians/token-get", methods={"GET"})
     *
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return App\Actions\SendResponse
     */
    public function getToken()
    {
        $token = Token::orderBy('id')->first();

        if($token) {
            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now());
            $from = $token->updated_at->format('Y-m-d H:i:s');
            $differ = $to->diffInSeconds($from);

            $setting_token = DB::table('settings')->where('name', 'token')->first();

            if (!$setting_token) {
                return SendResponse::badRequest('Kesalahan dalam installasi token');
            }

            $token_expired = intval(json_decode($setting_token->value));
            $token_expired = $token_expired ? $token_expired : 900;

            if($differ > $token_expired) {
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
     * @Route(path="api/v1/ujians/peserta/add-more-time", methods={"POST"})
     *
     * Tambah waktu ujian untuk siswa
     *
     * @param Illuminate\Http\Request $request
     * @return App\Actions\SendResponse
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
