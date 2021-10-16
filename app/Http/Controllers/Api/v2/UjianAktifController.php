<?php
namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Models\UjianConstant;
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
use \Illuminate\Support\Facades\DB;
use App\Actions\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\JawabanPeserta;
use App\SesiSchedule;
use App\SiswaUjian;
use Carbon\Carbon;
use App\Banksoal;
use App\HasilUjian;
use App\Jadwal;
use App\Token;
use App\Soal;

use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\Jadwal\JadwalService;
use ShellreanDev\Services\Ujian\UjianService as DevUjianService;

/**
 * UjianAktifController
 * @author shellrean <wandinak17@gmail.com>
 */
class UjianAktifController extends Controller
{
    /**
     * @Route(path="api/v2/ujians/uncomplete", methods={"GET"})
     *
     * Ambil data ujian siswa yang belum diselesaikan pada hari ini
     *
     * @param ShellreanDev\Services\Ujian\UjianService $ujianService
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function uncompleteUjian(DevUjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');

        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
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
     * @param  Illuminate\Http\Request $request
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function startUjian(Request $request, CacheHandler $cache)
    {
        // cari jadwal ujian yang diminta
//        $key = md5(sprintf('jadwal:data:%s:single', $request->jadwal_id));
//        if ($cache->isCached($key)) {
//            $ujian = $cache->getItem($key);
//        } else {
            $ujian = DB::table('jadwals')
                ->where('id', $request->jadwal_id)
                ->first();

//            $cache->cache($key, $ujian);
//        }

        // Jika ujian tidak ditemukan
        if (!$ujian) {
            return SendResponse::badRequest('jadwal yang diminta tidak ditemukan');
        }

        // jika token diaktifkan
        $setting = json_decode($ujian->setting, true);
        if($setting['token'] == "1") {

            // Ambil token
            $token = Token::orderBy('id')->first();
            if($token) {
                $to = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', now());
                $from = $token->updated_at->format('Y-m-d H:i:s');
                $differ = $to->diffInSeconds($from);

                // ambil setting token
//                $key = md5(sprintf('setting:token:single'));
//                if ($cache->isCached($key)) {
//                    $setting_token = $cache->getItem($key);
//                } else {
                    $setting_token = DB::table('settings')->where('name', 'token')->first();

//                    $cache->cache($key, $setting_token);
//                }
                if (!$setting_token) {
                    return SendResponse::badRequest('Kesalahan dalam installasi token, hubungi administrator');
                }

                $token_expired = intval($setting_token->value);
                $token_expired = $token_expired ? $token_expired : 900;

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

        // cek pengaturan sesi
        if($ujian->event_id != '0' && $ujian->event_id != null) {
            // ambil data sesi schedule
//            $key = md5(sprintf('sesi_schedules:jadwal:%s:sesi:%s', $ujian->id, $ujian->sesi));
//            if ($cache->isCached($key)) {
//                $schedule = $cache->getItem($key);
//            } else {
                $schedule = DB::table('sesi_schedules')
                    ->where([
                        'jadwal_id' => $ujian->id,
                        'sesi'      => $ujian->sesi
                    ])
                    ->first();

//                $cache->cache($key, $schedule);
//            }

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

        // ambil data siswa ujian
        // yang masih dalam mode standby
//        $key = md5(sprintf('siswa_ujians:peserta:%s:jadwal:%s:standby', $peserta->id, $request->jadwal_id));
//        if ($cache->isCached($key)) {
//            $data = $cache->getItem($key);
//        } else {
            $data = DB::table('siswa_ujians')
                ->where('peserta_id', $peserta->id)
                ->where('jadwal_id', $request->jadwal_id)
                ->where('status_ujian', 0)
                ->first();

//            $cache->cache($key, $data);
//        }

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
                'status_ujian'      => 0,
                'uploaded'          => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $data = DB::table('siswa_ujians')
                ->where('id', $siswa_ujian_id)
                ->first();

//            $cache->cache($key, $data);

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
     * @param ShellreanDev\Services\Ujian\UjianService $ujianService
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getUjianPesertaAktif(DevUjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');

        // ambil data siswa ujian
        // yang sudah dijalankan pada hari ini
        // tetapi belum dimulai
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = $ujianService->onStandbyToday($peserta->id);

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
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function startUjianTime()
    {
        $peserta = request()->get('peserta-auth');

        // Ambil data yang belum dimulai
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $peserta->id)
            ->where('status_ujian', '<>', 1)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        if (!$data) {
            return SendResponse::badRequest('Kami tidak dapat mengambil ujian untuk kamu, kamu tidak sedang mengikuti ujian apapun. silakan logout lalu login kembali');
        }

        // Jika ini adalah pertama kali peserta
        // Melakukan mulai ujian
        // 3 <= sedang mengerjakan
        if ($data->status_ujian != 3) {
            try {
                DB::table('siswa_ujians')
                    ->where('id', $data->id)
                    ->update([
                        'mulai_ujian'       => now()->format('H:i:s'),
                        'mulai_ujian_shadow'=> now()->format('H:i:s'),
                        'status_ujian'      => 3,
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
     * @param ShellreanDev\Services\Ujian\UjianService $ujianService
     * @return \Illuminate\Http\Response
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getJawabanPeserta(DevUjianService $devUjianService, CacheHandler $cache)
    {
        $peserta = request()->get('peserta-auth');
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
            ->select('banksoals.id','matpels.agama_id','matpels.jurusan_id')
            ->get();

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
            $unique_num = 1;
            foreach ($soals as $soal) {
                $soal['created_at'] = now()->addSeconds($unique_num);
                $new_soals[] = $soal;
                $unique_num ++;
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
            ->where([
                'jadwal_id'     => $jadwal->id,
                'peserta_id'    => $peserta->id
            ])
            ->first();

        # Check perbedaan waktu
        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian_shadow);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        # Jika perbedaan waktu telah melebihi waktu pengerjaan ujian
        if($diff_in_minutes > $jadwal->lama) {
            try {
                DB::beginTransaction();
                $ujian->status_ujian = 1;
                $ujian->save();

                $devUjianService->finishing($banksoal_id, $jadwal->id, $peserta->id);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }
        } else {
            try {
                DB::beginTransaction();
                $ujian->sisa_waktu = $jadwal->lama-$diff_in_minutes;
                $ujian->save();

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return SendResponse::internalServerError($e->getMessage());
            }
        }

        return SendResponse::acceptCustom(['data' => $jawaban_peserta, 'detail' => $ujian]);
    }

    /**
     * @Route(path="api/v2/ujian/hasils", methods={"GET"})
     *
     * Hasil ujian siswa
     *
     * @param ShellreanDev\Services\JadwalService $jadwalService
     * @return App\Actions\SendResponse
     * @author shellrean <wandinak17@gmail.com>
     */
    public function getHasilUjian(JadwalService $jadwalService)
    {
        $peserta = request()->get('peserta-auth');

        // ambil ujian yang aktif hari ini
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

        // Ambil hasil ujian siswa
        $hasil = DB::table('hasil_ujians')
            ->whereIn('hasil_ujians.jadwal_id', $viewable)
            ->where('hasil_ujians.peserta_id', $peserta->id)
            ->join('jadwals', 'jadwals.id', '=', 'hasil_ujians.jadwal_id')
            ->select([
                'jadwals.alias',
                'hasil_ujians.hasil'
            ])
            ->get();

        return SendResponse::acceptData($hasil);
    }
}
