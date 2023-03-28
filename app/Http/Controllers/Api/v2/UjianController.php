<?php

namespace App\Http\Controllers\Api\v2;

use App\Models\UjianConstant;
use App\Services\Ujian\BenarSalahService;
use App\Services\Ujian\EsayService;
use App\Services\Ujian\IsianSingkatService;
use App\Services\Ujian\JawabanPesertaService;
use App\Services\Ujian\MengurutkanService;
use App\Services\Ujian\MenjodohkanService;
use App\Services\Ujian\PilihanGandaKomplekService;
use App\Services\Ujian\PilihanGandaService;
use App\Services\Ujian\SetujuTidakService;
use App\Actions\SendResponse;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use ShellreanDev\Services\Ujian\UjianService;

/**
 * UjianController
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 2.0.1 <latte>
 */
class UjianController extends Controller
{
    /**
     * @Route(path="api/v2/ujian", methods={"POST"})
     *
     * Simpan/Update jawaban siswa pada ujian aktif
     *
     * @param Request $request
     * @param UjianService $ujianService
     * @param JawabanPesertaService $jawabanPesertaService
     * @return Response
     * @throws Exception
     */
    public function store(Request $request, UjianService $ujianService, JawabanPesertaService  $jawabanPesertaService)
    {
        $request->validate([
            'jawaban_id'    => 'required',
            'index'         => 'required'
        ]);

        $peserta = request()->get('peserta-auth');

        $find = $jawabanPesertaService->getJawaban($request->jawaban_id);

        if (!$find) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_ANSWER_FOUND);
        }

        # ambil data siswa ujian
        # yang sedang dikerjakan pada hari ini
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $ujian = $ujianService->onProgressToday($peserta->id);

        if (!$ujian) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_UJIAN_FOUND);
        }

        # kurangi waktu ujian
        $ujianService->updateReminingTime($ujian);

        # Jika yang dikirimkan adalah esay
        if(isset($request->essy)) {
            return EsayService::setJawab($request, $find);
        }

        # Jika yang dikirimkan adalah isian singkat
        if(isset($request->isian)) {
            return IsianSingkatService::setJawab($request, $find);
        }

        # Jika yang dikirimkan adalah jawaban komleks
        if(is_array($request->jawab_complex)) {
            return PilihanGandaKomplekService::setJawab($request, $find);
        }

        # Jika yang dikirimkan adalah menjodohkan
        if(isset($request->menjodohkan)) {
            return MenjodohkanService::setJawab($request, $find);
        }

        # Jika yang dikirimkan adalah mengurutkan
        if(isset($request->mengurutkan)) {
            return MengurutkanService::setJawab($request, $find);
        }

        # jika yang dikirimkan adalah salah/benar
        if(is_array($request->benar_salah)) {
            return BenarSalahService::setJawab($request, $find);
        }

        # Jika yang dikirimkan adalah setuju/tidak
        if(isset($request->setuju_tidak)) {
            return SetujuTidakService::setJawab($request, $find);
        }

        # Jika yang dikirimkan adalah pilihan ganda
        return PilihanGandaService::setJawab($request, $find);
    }

    /**
     * @Route(path="api/v2/ujian/ragu-ragu", methods={"POST"})
     *
     * Set ragu ragu in siswa
     *
     * @param Request $request
     * @param UjianService $ujianService
     * @param JawabanPesertaService $jawabanPesertaService
     * @return Response
     * @throws Exception
     */
    public function setRagu(Request $request, UjianService $ujianService, JawabanPesertaService $jawabanPesertaService)
    {
        $peserta = request()->get('peserta-auth');

        $find = $jawabanPesertaService->getJawaban($request->jawaban_id);

        if (!$find) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_ANSWER_FOUND);
        }

        if(!isset($request->ragu_ragu)) {
            return SendResponse::acceptCustom([
                'data' => ['jawab' => $find->jawab],
                'index' => $request->index
            ]);
        }

        # ambil data siswa ujian
        # yang sedang dikerjakan pada hari ini
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $ujian = $ujianService->onProgressToday($peserta->id);

        if (!$ujian) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_UJIAN_FOUND);
        }

        # update sisa waktu ujian
        $ujianService->updateReminingTime($ujian);

        try {
            DB::table('jawaban_pesertas')
                ->where('id', $find->id)
                ->update([
                    'ragu_ragu' => $request->ragu_ragu
                ]);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }

        return SendResponse::acceptCustom(['data' => ['jawab' => $find->jawab ],'index' => $request->index]);
    }

    /**
     * @Route(path="api/v2/ujian/selesai", methods={"GET"})
     *
     * Selesaikan ujian
     *
     * @param UjianService $ujianService
     * @return Response
     */
    public function selesai(UjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');

        # ambil data siswa ujian
        # yang sedang dikerjakan pada hari ini
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $ujian = $ujianService->onProgressToday($peserta->id);

        if (!$ujian) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_UJIAN_FOUND);
        }

        # Cek apakah hasil ujian pernah di generate sebelumnya
        $hasilUjian = DB::table('hasil_ujians')
            ->where([
                'peserta_id'    => $peserta->id,
                'jadwal_id'     => $ujian->jadwal_id,
            ])
            ->count();

        if($hasilUjian > 0) {
            try {
                DB::table('siswa_ujians')
                    ->where('id', $ujian->id)
                    ->update([
                        'status_ujian'  => UjianConstant::STATUS_FINISHED
                    ]);

                return SendResponse::badRequest(UjianConstant::WARN_UJIAN_HAS_FINISHED_BEFORE);
            } catch (Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }
        }

        # validate minimum time
        $start = Carbon::createFromFormat('H:i:s', $ujian->mulai_ujian_shadow);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        $jadwal = DB::table("jadwals")->where("id", $ujian->jadwal_id)->first();
        if($diff_in_minutes < ($jadwal->min_test*60)) {
            return SendResponse::badRequest(UjianConstant::MINUMUM_TEST_INVALID." min:".$jadwal->min_test." menit");
        }

        # ambil hanya banksoal untuk jawaban peserta pertama
        $jawaban = DB::table('jawaban_pesertas')
            ->where([
                'jadwal_id'     => $ujian->jadwal_id,
                'peserta_id'    => $peserta->id
            ])
            ->select('banksoal_id')
            ->first();

        if (!$jawaban) {
            return SendResponse::badRequest(UjianConstant::NO_WORKING_UJIAN_FOUND);
        }

        try {
            DB::beginTransaction();

            $ujianService->finishing($jawaban->banksoal_id, $ujian->jadwal_id, $peserta->id, $ujian->id);

            DB::table('siswa_ujians')
                ->where('id', $ujian->id)
                ->update([
                    'status_ujian'  => UjianConstant::STATUS_FINISHED,
                    'selesai_ujian' => now()->format('H:i:s')
                ]);
            DB::commit();

            return SendResponse::accept('ujian berhasil diselesaikan');
        } catch (Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
    }
}

