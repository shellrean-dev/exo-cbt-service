<?php
namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\CacheConstant;
use App\Models\UjianConstant;
use App\Services\Setting\SettingTokenService;
use App\Services\Ujian\BenarSalahService;
use App\Services\Ujian\EsayService;
use App\Services\Ujian\IsianSingkatService;
use App\Services\Ujian\ListeningService;
use App\Services\Ujian\MengurutkanService;
use App\Services\Ujian\MenjodohkanService;
use App\Services\Ujian\PilihanGandaKomplekService;
use App\Services\Ujian\PilihanGandaService;
use App\Services\Ujian\SetujuTidakService;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Token;

use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\Jadwal\JadwalService;
use ShellreanDev\Services\Ujian\UjianService;
use ShellreanDev\Services\Ujian\UjianService as DevUjianService;

/**
 * UjianAktifController
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 1.0.0 <expresso>
 */
class UjianAktifController extends Controller
{
    /**
     * @Route(path="api/v2/ujians/uncomplete", methods={"GET"})
     *
     * Ambil data ujian siswa yang belum diselesaikan pada hari ini
     *
     * @param DevUjianService $ujianService
     * @return Response
     *
     * @author shellrean <wandinak17@gmail.com>
     */
    public function uncompleteUjian(DevUjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');

        # ambil data siswa ujian
        # yang sedang dikerjakan pada hari ini
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = $ujianService->onWorkingToday($peserta->id);

        if(!$data) {
            return SendResponse::acceptData([]);
        }

        $res = [
            'jadwal_id'     => $data->jadwal_id,
            'status_ujian'  => $data->status_ujian
        ];

        return SendResponse::acceptData($res);
    }

    /**
     * @Route(path="api/v2/ujians/start", methods={"POST"})
     *
     * Memulai ujian masuk kedalam mode standby
     *
     * @param Request $request
     * @param CacheHandler $cache
     * @return Response
     *
     * @author shellrean <wandinak17@gmail.com>
     */
    public function startUjian(Request $request, UjianService $ujianService, SettingTokenService  $tokenService)
    {
        $ujian = DB::table('jadwals')
            ->where('id', $request->jadwal_id)
            ->first();

        # Jika ujian tidak ditemukan
        if (!$ujian) {
            return SendResponse::badRequest('jadwal yang diminta tidak ditemukan');
        }

        # jika token diaktifkan
        $setting = json_decode($ujian->setting, true);
        if($setting['token'] == "1") {

            # Ambil token
            $token = Token::orderBy('id')->first();
            if($token) {
                $to = Carbon::createFromFormat('Y-m-d H:i:s', now());
                $from = $token->updated_at->format('Y-m-d H:i:s');
                $differ = $to->diffInSeconds($from);

                $setting_token = $tokenService->getSetting();

                if (!$setting_token) {
                    return SendResponse::badRequest('Kesalahan dalam installasi token, hubungi administrator');
                }

                $token_expired = intval(json_decode($setting_token->value));
                $token_expired = $token_expired ?: 900;

                if($differ > $token_expired) {
                    $token->token = strtoupper(Str::random(6));
                    $token->status = '0';
                    $token->save();
                }
                if($token->token != $request->token) {
                    return SendResponse::badRequest('Token yang kamu masukkan tidak sesuai, cek token lalu submit kembali');
                }
                if($token->status == 0) {
                    return SendResponse::badRequest('Status token belum dirilis, minta administrator untuk merilis token');
                }
            } else {
                DB::table('tokens')->insert([
                    'token'     => strtoupper(Str::random(6)),
                    'status'    => '0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return SendResponse::badRequest('Token yang kamu masukkan tidak sesuai, cek token lalu submit kembali');
            }
        }
        $peserta = request()->get('peserta-auth');

        # Invalidate cache in progress
        $ujianService->deactivateCacheOnProgressToday($peserta->id);

        # cek pengaturan sesi
        if($ujian->event_id != '0' && $ujian->event_id != null) {
            $schedule = DB::table('sesi_schedules')
                ->where([
                    'jadwal_id' => $ujian->id,
                    'sesi'      => $ujian->sesi
                ])
                ->first();

            if($schedule) {
                if(!in_array($peserta->id, json_decode($schedule->peserta_ids, true))){
                    return SendResponse::badRequest('Anda tidak ada di dalam sesi '.$ujian->sesi.' bila anda merasa seharusnya berada di sesi ini, hubungi administrator');
                }
            } else {
                return SendResponse::badRequest('Sesi belum ditentukan, hubungi administrator');
            }
        } else {
            if($peserta->sesi != $ujian->sesi) {
                return SendResponse::badRequest('Anda tidak ada didalam sesi '.$ujian->sesi.' bila anda merasa seharusnya berada di sesi ini, hubungi administrator');
            }
        }

        # ambil data siswa ujian yang masih dalam mode standby
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('jadwal_id', $request->jadwal_id)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        if($data) {
            return SendResponse::accept('mata ujian diambil dari data sebelumnya');
        }

        try {
            $siswa_ujian_id = Str::uuid()->toString();

            DB::table('siswa_ujians')->insert([
                'id'                => $siswa_ujian_id,
                'peserta_id'        => $peserta->id,
                'jadwal_id'         => $request->jadwal_id,
                'mulai_ujian'       => '',
                'mulai_ujian_shadow'=> '',
                'sisa_waktu'        => $ujian->lama,
                'status_ujian'      => UjianConstant::STATUS_STANDBY,
                'uploaded'          => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

        } catch (Exception $e) {
            return SendResponse::internalServerError("Terjadi kesalahan 500. ".$e->getMessage());
        }

        return SendResponse::accept('mata ujian diambil dengan mulai ujian baru');
    }

    /**
     * @Route(path="api/v2/ujians/peserta", methods={"GET"})
     *
     * Ambil ujian peserta yang sedang dikerjakan
     *
     * @param DevUjianService $ujianService
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getUjianPesertaAktif(DevUjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');

        # ambil data siswa ujian
        # yang sudah dijalankan pada hari ini
        # tetapi belum dimulai
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = $ujianService->onWorkingToday($peserta->id);

        if(!$data) {
            return SendResponse::acceptData([]);
        }

        $res = [
            'jadwal_id'     => $data->jadwal_id,
            'status_ujian'  => $data->status_ujian
        ];

        return SendResponse::acceptData($res);
    }

    /**
     * @Route(path="api/v2/ujians/start/time", methods={"POST"})
     *
     * Mulai penghitungan waktu ujian
     *
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function startUjianTime(JadwalService $jadwalService)
    {
        $peserta = request()->get('peserta-auth');

        # ambil ujian yang aktif hari ini
        $jadwals = $jadwalService->activeToday();
        $jadwal_ids = $jadwals->pluck('id')->toArray();

        # Ambil data yang belum dimulai
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->whereIn('status_ujian', [UjianConstant::STATUS_STANDBY, UjianConstant::STATUS_PROGRESS])
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->whereIn('jadwal_id', $jadwal_ids)
            ->orderBy('created_at')
            ->orderByDesc('status_ujian')
            ->first();

        if (!$data) {
            return SendResponse::badRequest(UjianConstant::NO_CURRENT_UJIAN_EXIST);
        }

        # Jika ini adalah pertama kali peserta
        # Melakukan mulai ujian
        # 3 <= sedang mengerjakan
        if (intval($data->status_ujian) != UjianConstant::STATUS_PROGRESS) {
            try {
                DB::table('siswa_ujians')
                    ->where('id', $data->id)
                    ->update([
                        'mulai_ujian'       => now()->format('H:i:s'),
                        'mulai_ujian_shadow'=> now()->format('H:i:s'),
                        'status_ujian'      => UjianConstant::STATUS_PROGRESS,
                    ]);
            } catch(Exception $e){
                return SendResponse::internalServerError($e->getMessage());
            }
        }
        return SendResponse::accept();
    }

    /**
     * @Route(path="api/v2/ujians/filled", methods={"GET"})
     *
     * Ambil soal dan jawaban siswa
     *
     * @param DevUjianService $devUjianService
     * @param CacheHandler $cache
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getJawabanPeserta(DevUjianService $devUjianService, CacheHandler $cache)
    {
        $peserta = request()->get('peserta-auth');
        $devUjianService->deactivateCacheOnProgressToday($peserta->id);
        $ujian_siswa = $devUjianService->onProgressToday($peserta->id);

        if(!$ujian_siswa) {
            return SendResponse::badRequest(UjianConstant::NO_CURRENT_UJIAN_EXIST);
        }

        # Ambil id banksoal yang terkait dalam jadwal
        $jadwal = DB::table('jadwals')
            ->where('id', $ujian_siswa->jadwal_id)
            ->first();

        # Jika jadwal yang dikerjakan siswa tidak ditemukan
        if(!$jadwal) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_UJIAN_FOUND);
        }

        $banksoal_ids = array_column(json_decode($jadwal->banksoal_id, true), 'id');

        # ambil data banksoal yang diujikan pada jadwal
        $banksoal_diujikan = DB::table('banksoals')
            ->join('matpels','banksoals.matpel_id','=','matpels.id')
            ->whereIn('banksoals.id', $banksoal_ids)
            ->select('banksoals.id','matpels.agama_id','matpels.jurusan_id');

        # checking cache
        if (config('exo.enable_cache')) {
            $cache_key_ids = implode('-', $banksoal_ids);
            $is_cached = $cache->isCached(CacheConstant::KEY_BANKSOAL_IN_UJIAN_ACTIVE, $cache_key_ids);
            if ($is_cached) {
                $banksoal_diujikan = $cache->getItem(CacheConstant::KEY_BANKSOAL_IN_UJIAN_ACTIVE, $cache_key_ids);
            } else {
                $banksoal_diujikan = $banksoal_diujikan->get();
                if($banksoal_diujikan) {
                    $cache->cache(CacheConstant::KEY_BANKSOAL_IN_UJIAN_ACTIVE, $cache_key_ids, $banksoal_diujikan);
                }
            }
        } else {
            $banksoal_diujikan = $banksoal_diujikan->get();
        }

        $banksoal_id = '';
        # Cari id banksoal yang dapat dipakai oleh siswwa
        foreach($banksoal_diujikan as $bk) {
            $banksoal = $devUjianService->checkPesertaBanksoal($bk, $peserta);
            if (!$banksoal) {
                continue;
            }
            $banksoal_id = $banksoal;
            break;
        }

        # Jika tidak dapat menemukan banksoal_id
        if($banksoal_id == '') {
            return SendResponse::badRequest(UjianConstant::NO_BANKSOAL_FOR_YOU);
        }

        # Ambil jawaban siswa yang telah digenerate
        $jawaban_peserta = $devUjianService->pesertaAnswers(
            $jadwal->id,
            $peserta->id,
            json_decode($jadwal->setting, true)['acak_opsi']
        );

        # Jika jawaban siswa belum ada di database
        if (count($jawaban_peserta) < 1 ) {
            $banksoal = DB::table('banksoals')
                ->where('id', $banksoal_id)
                ->first();

            # Ambil setting dari jadwal
            $setting = json_decode($jadwal->setting, true);

            # Ambil soal tipe : ganda
            $soal_pg = PilihanGandaService::getSoal($peserta, $banksoal, $jadwal);

            # Ambil soal tipe :esay
            $soal_esay = EsayService::getSoal($peserta, $banksoal, $jadwal);

            # Ambil soal: Listening
            $soal_listening = ListeningService::getSoal($peserta, $banksoal, $jadwal);

            # Ambil soal: Multichoice complex
            $soal_complex = PilihanGandaKomplekService::getSoal($peserta, $banksoal, $jadwal);

            # Ambil soal:  menjodohkan
            $soal_menjodohkan = MenjodohkanService::getSoal($peserta, $banksoal, $jadwal);

            # Ambil soal:  isian singkat
            $soal_isian_singkat = IsianSingkatService::getSoal($peserta, $banksoal, $jadwal);

            # Soal mengurutkan
            $soal_mengurutkan = MengurutkanService::getSoal($peserta, $banksoal, $jadwal);

            # Soal benar-salah
            $soal_benar_salah = BenarSalahService::getSoal($peserta, $banksoal, $jadwal);

            # Soal setuju-tidak
            $soal_setuju_tidak = SetujuTidakService::getSoal($peserta, $banksoal, $jadwal);

            # Gabungkan semua collection dari tipe soal
            $soals = [];
            $list = collect([
                '1' => $soal_pg,
                '2' => $soal_esay,
                '3' => $soal_listening,
                '4' => $soal_complex,
                '5' => $soal_menjodohkan,
                '6' => $soal_isian_singkat,
                '7' => $soal_mengurutkan,
                '8' => $soal_benar_salah,
                '9' => $soal_setuju_tidak,
            ]);
            foreach ($setting['list'] as $value) {
                $soal = $list->get($value['id']);
                if($soal) {
                    $soals = array_merge($soals, $soal);
                }
            }

            $new_soals = [];
            $time_offset = 1;
            foreach ($soals as $key => $soal) {
                $new_soals[$key] = $soal;
                $new_soals[$key]['created_at'] = now()->addSeconds($time_offset);

                $time_offset++;
            }

            # Insert ke database sebagai jawaban siswa
            try {
                DB::beginTransaction();
                DB::table('jawaban_pesertas')->insert($new_soals);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }

            # Ambil jawaban siswa
            $jawaban_peserta = $devUjianService->pesertaAnswers(
                $jadwal->id,
                $peserta->id,
                $setting['acak_opsi']
            );

            return SendResponse::acceptCustom(['data' => $jawaban_peserta, 'detail' => $ujian_siswa]);
        }

        # Get siswa ujian detail
        $ujian = DB::table('siswa_ujians')
            ->where('jadwal_id', $jadwal->id)
            ->where('peserta_id', $peserta->id)
            ->where('status_ujian', '=', UjianConstant::STATUS_PROGRESS)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        # Check perbedaan waktu
        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian_shadow);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        # Jika perbedaan waktu telah melebihi waktu pengerjaan ujian
        if($diff_in_minutes > $jadwal->lama) {
            try {
                DB::beginTransaction();

                $hasilUjian = DB::table('hasil_ujians')->where([
                    'peserta_id'    => $peserta->id,
                    'jadwal_id'     => $jadwal->id,
                ])->count();

                if (!$hasilUjian) {
                    $devUjianService->finishing($banksoal_id, $jadwal->id, $peserta->id, $ujian->id);
                }

                DB::table('siswa_ujians')
                    ->where('id', $ujian->id)
                    ->update([
                        'status_ujian' => UjianConstant::STATUS_FINISHED,
                        'selesai_ujian' => now()->format('H:i:s')
                    ]);

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }
        } else {
            try {
                DB::beginTransaction();

                DB::table('siswa_ujians')
                    ->where('id', $ujian->id)
                    ->update(['sisa_waktu' => $jadwal->lama-$diff_in_minutes]);

                DB::commit();

                $ujian->sisa_waktu = $jadwal->lama-$diff_in_minutes;
                return SendResponse::acceptCustom(['data' => $jawaban_peserta, 'detail' => $ujian]);
            } catch (Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }
        }
    }

    /**
     * @Route(path="api/v2/ujian/hasils", methods={"GET"})
     *
     * Hasil ujian siswa
     *
     * @param JadwalService $jadwalService
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getHasilUjian(JadwalService $jadwalService)
    {
        $peserta = request()->get('peserta-auth');

        # ambil ujian yang aktif hari ini
        $jadwals = $jadwalService->activeToday();

        $viewable = [];
        foreach ($jadwals as $jadwal) {
            if ($jadwal->view_result == 1) {
                $viewable[] = $jadwal->id;
            }
        }

        if (count($viewable) < 1) {
            return SendResponse::acceptData([]);
        }

        # Ambil hasil ujian siswa
        $hasil = DB::table('hasil_ujians')
            ->whereIn('hasil_ujians.jadwal_id', $viewable)
            ->where('hasil_ujians.peserta_id', $peserta->id)
            ->join('jadwals', 'jadwals.id', '=', 'hasil_ujians.jadwal_id')
            ->select([
                'jadwals.alias',
                'hasil_ujians.point_esay',
                'hasil_ujians.point_setuju_tidak',
                'hasil_ujians.hasil as result'
            ])
            ->get();

        $hasil = $hasil->map(function($item) {
            return [
                'alias' => $item->alias,
                'hasil' => $item->point_esay + $item->point_setuju_tidak + $item->result
            ];
        });

        return SendResponse::acceptData($hasil);
    }

    /**
     * @Route(path="api/v2/ujians/leave-counter", methods={"POST"})
     *
     * Leave counter update
     *
     * @param Request $request
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function leaveCounter(Request $request)
    {
        $settings = DB::table('settings')->where('name', 'ujian')->first();
        if(!$settings) {
            return SendResponse::acceptData([
                'status' => 1,
                'block_reason' => ''
            ]);
        }
        $sett_value = json_decode($settings->value);
        if(!isset($sett_value->autoblock) ||  !$sett_value->autoblock) {
            return SendResponse::acceptData([
                'status' => 1,
                'block_reason' => ''
            ]);
        }

        $ujian = DB::table('siswa_ujians')->where('id', $request->id)->first();
        $peserta = request()->get('peserta-auth');
        if($ujian && !$peserta->antiblock) {
            $count = $ujian->out_ujian_counter + 1;
            DB::table('siswa_ujians')->where('id', $ujian->id)->update([
                'out_ujian_counter' => $count
            ]);

            if($count > 3) {
                DB::table('pesertas')->where('id', $ujian->peserta_id)->update([
                    'status' => 0,
                    'api_token' => null,
                    'block_reason' => 'Terlalu sering keluar ujian'
                ]);
                return SendResponse::acceptData([
                    'status' => 0,
                    'block_reason' => 'Terlalu sering keluar ujian'
                ]);
            }
        }
        return SendResponse::acceptData([
            'status' => 1,
            'block_reason' => ''
        ]);
    }

    /**
     * @Route(path="api/v2/ujians/block-me-please", methods={"POST"})
     *
     * Please block me
     *
     * @param Request $request
     * @return Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function blockMePlease(Request $request)
    {
        $ujian = DB::table('siswa_ujians')->where('id', $request->id)->first();
        $peserta = request()->get('peserta-auth');
        if($ujian && !$peserta->antiblock) {
            DB::table('pesertas')->where('id', $ujian->peserta_id)->update([
                'status'        => 0,
                'api_token'     => null,
                'block_reason' => $request->reason
            ]);
        }
    }
}
