<?php

namespace App\Http\Controllers\Api\v2;

use App\Soal;
use App\Actions\SendResponse;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\Ujian\UjianService;

/**
 * UjianController
 * @author shellrean <wandinak17@gmail.com>
 */
class UjianController extends Controller
{
    /**
     * @Route(path="api/v2/ujian", methods={"POST"})
     *
     * Simpan/Update jawaban siswa pada ujian aktif
     *
     * @param Illuminate\Http\Request $request
     * @param ShellreanDev\Services\UjianService $ujianService
     * @param Shellreandev\Cache\CacheHandler $cache
     * @return Illuminate\Http\Response
     * @author shellrean <wandnak17@gmail.com>
     */
    public function store(Request $request, UjianService $ujianService, CacheHandler $cache)
    {
        $request->validate([
            'jawaban_id' => 'required',
            'index'     => 'required'
        ]);

        $peserta = request()->get('peserta-auth');

        // Ambil jawaban peserta
//        $key = md5(sprintf('jawaban_pesertas:jawab:%s:single', $request->jawaban_id));
//        if ($cache->isCached($key)) {
//            $find = $cache->getItem($key);
//        } else {
            $find = DB::table('jawaban_pesertas')
                ->where('id', $request->jawaban_id)
                ->first();

//            $cache->cache($key, $find);
//        }

        if (!$find) {
            return SendResponse::badRequest('Kami tidak dapat menemukan data dari jawaban kamu.');
        }

        // ambil ujian yang aktif hari ini
        // $ujian = $this->_getUjianCurrent($peserta);
        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $ujian = $ujianService->onProgressToday($peserta->id);

        if (!$ujian) {
            return SendResponse::badRequest('Kami tidak dapat menemukan ujian yang sedang kamu kerjakan, mungkin jadawal ini sedang tidak aktif. silakan logout lalu hubungi administrator.');
        }

        // kurangi waktu ujian
        $ujianService->updateReminingTime($ujian);

        // Jika yang dikirimkan adalah esay
        if(isset($request->essy)) {
            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'esay'  => $request->essy
                    ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'menjodohkan'   => json_decode($find->menjodohkan, true),
                'esay'          => $request->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah isian singkat
        if(isset($request->isian)) {
            // ambil jawaban soal
//            $key = md5(sprintf('jawaban_soals:datas:soal:%s', $find->soal_id));
//            if ($cache->isCached($key)) {
//                $jwb_soals = $cache->getItem($key);
//            } else {
                $jwb_soals = DB::table('jawaban_soals')
                    ->where('soal_id', $find->soal_id)
                    ->select(['id', 'text_jawaban'])
                    ->get();
                $soal = DB::table('soals')
                    ->where('id', $find->soal_id)
                    ->select('id', 'case_sensitive')
                    ->first();
//                $cache->cache($key, $jwb_soals);
//            }

            foreach($jwb_soals as $jwb) {
                $jwb_strip = strip_tags($jwb->text_jawaban);
                $isian_siswa = $request->isian;
                if ($soal->case_sensitive == '0') {
                    $jwb_strip = strtoupper($jwb_strip);
                    $isian_siswa = strtoupper($isian_siswa);
                }
                if (trim($jwb_strip) == trim($isian_siswa)) {
                    $find->iscorrect = 1;
                    break;
                }
                $find->iscorrect = 0;
            }

            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'iscorrect' => $find->iscorrect,
                        'esay'      => $request->isian,
                    ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'menjodohkan'   => json_decode($find->menjodohkan, true),
                'esay'          => $find->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah jawaban komleks
        if(is_array($request->jawab_complex)) {
            // ambil soal complex
//            $key = md5(sprintf('jawabans:data:%s:relation:jawabans', $find->soal_id));
//            if ($cache->isCached($key)) {
//                $soal_complex = $cache->getItem($key);
//            } else {
                $soal_complex = Soal::with(['jawabans' => function($query) {
                    $query->where('correct', 1);
                }])
                ->where("id", $find->soal_id)
                ->first();

//                $cache->cache($key, $soal_complex);
//            }

            if ($soal_complex) {
                $array = $soal_complex->jawabans->map(function($item){
                    return $item->id;
                })->toArray();
                $correct = 0;
                $complex = array_diff( $request->jawab_complex, [0] );
                if (array_diff($array,$complex) == array_diff($complex,$array)) {
                    $correct = 1;
                }
                $find->iscorrect = $correct;
            }

            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'jawab_complex' => json_encode($request->jawab_complex),
                        'iscorrect'     => $find->iscorrect,
                    ]);
                $find->jawab_complex = json_encode($request->jawab_complex);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'menjodohkan'   => json_decode($find->menjodohkan, true),
                'esay'          => $find->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah menjodohkan
        if(isset($request->menjodohkan)) {
            $jwb_soals = DB::table('jawaban_soals')
                ->where('soal_id', $find->soal_id)
                ->get();
            $menjodohkan_correct = $jwb_soals->map(function($item) {
                $obj = json_decode($item->text_jawaban, true);
                return [$obj['a']['id'], $obj['b']['id']];
            });
            $count_corect = 0;
            foreach ($request->menjodohkan as $result) {
                foreach ($menjodohkan_correct as $item) {
                    if ($result == $item) {
                        $count_corect += 1;
                        break;
                    }
                }
            }
            $result_menjodohkan_correct = 0;
            if ($count_corect == $menjodohkan_correct->count()) {
                $result_menjodohkan_correct = 1;
            }
            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'iscorrect' => $result_menjodohkan_correct,
                        'menjodohkan' => json_encode($request->menjodohkan)
                    ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'menjodohkan'   => json_decode($find->menjodohkan, true),
                'esay'          => $find->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah mengurutkan
        if(isset($request->mengurutkan)) {
            $jwb_soals = DB::table('jawaban_soals')
                ->where('soal_id', $find->soal_id)
                ->orderBy('created_at')
                ->get();
            $mengurutkan_correct = $jwb_soals->map(function($item) {
                return $item->id;
            });

            $result_mengurutkan_correct = 1;
            for ($i = 0; $i < count($mengurutkan_correct); $i++) {
                if ($mengurutkan_correct[$i] != $request->mengurutkan[$i]) {
                    $result_mengurutkan_correct = 0;
                    break;
                }
            }
            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'iscorrect' => $result_mengurutkan_correct,
                        'mengurutkan' => json_encode($request->mengurutkan)
                    ]);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'mengurutkan'   => json_decode($find->mengurutkan, true),
                'esay'          => $find->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];

            return response()->json(['data' => $send,'index' => $request->index]);
        }

        # jia yang dikirimkan adalah salah/benar
        if(is_array($request->benar_salah)) {
            $soal_benar_salah = DB::table('soals as s')
                ->join('jawaban_soals as j', 'j.soal_id', '=','s.id')
                ->select('j.id as jawaban_id', 'correct')
                ->where('s.id', $find->soal_id)
                ->get();

            $count_corect = 0;
            foreach ($request->benar_salah as $k => $v) {
                $seachSoal = $soal_benar_salah->where('jawaban_id', $k)->first();
                if ($seachSoal && ($seachSoal->correct == $v)) {
                    $count_corect += 1;
                }
            }

            $find->iscorrect = 0;
            if (count($soal_benar_salah) == $count_corect) {
                $find->iscorrect = 1;
            }

            try {
                DB::table('jawaban_pesertas')
                    ->where('id', $find->id)
                    ->update([
                        'benar_salah' => json_encode($request->benar_salah),
                        'iscorrect'     => $find->iscorrect,
                    ]);
                $find->benar_salah = json_encode($request->benar_salah);
            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }

            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'benar_salah' => json_decode($find->benar_salah, true),
                'esay'          => $find->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        // Jika yang dikirimkan adalah pilihan ganda
//        $key = md5(sprintf('jawaban_soals:data:%s:only:correct', $request->jawab));
//        if ($cache->isCached($key)) {
//            $kj = $cache->getItem($key);
//        } else {
            $kj = DB::table('jawaban_soals')
                ->where('id', $request->jawab)
                ->select('correct')
                ->first();

//            $cache->cache($key, $kj);
//        }
        if(!$kj) {
            $send = [
                'id'            => $find->id,
                'banksoal_id'   => $find->banksoal_id,
                'soal_id'       => $find->soal_id,
                'jawab'         => $find->jawab,
                'jawab_complex' => json_decode($find->jawab_complex, true),
                'esay'          => $find->esay,
                'ragu_ragu'     => $find->ragu_ragu,
            ];
            return response()->json(['data' => $send,'index' => $request->index]);
        }

        try {
            DB::table('jawaban_pesertas')
                ->where('id', $find->id)
                ->update([
                    'jawab'         => $request->jawab,
                    'iscorrect'     => $kj->correct,
                ]);
            $find->jawab = $request->jawab;
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }

        $send = [
            'id'            => $find->id,
            'banksoal_id'   => $find->banksoal_id,
            'soal_id'       => $find->soal_id,
            'jawab'         => $find->jawab,
            'jawab_complex' => json_decode($find->jawab_complex, true),
            'esay'          => $find->esay,
            'ragu_ragu'     => $find->ragu_ragu,
        ];
    	return response()->json(['data' => $send,'index' => $request->index]);

    }

    /**
     * @Route(path="api/v2/ujian/ragu-ragu", methods={"POST"})
     *
     * Set ragu ragu in siswa
     *
     * @param Illuminate\Http\Request $request
     * @param ShellreanDev\Services\UjianService $ujianService
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @return Illuminate\Http\Response
     * @author shellrean <wandnak17@gmail.com>
     */
    public function setRagu(Request $request, UjianService $ujianService, CacheHandler $cache)
    {
        $peserta = request()->get('peserta-auth');

        // ambil jawaban peserta
        $key = md5(sprintf('jawaban_pesertas:data:%s:single:%s', $request->jawaban_id, __METHOD__));
//        if ($cache->isCached($key)) {
//            $find = $cache->getItem($key);
//        } else {
            $find = DB::table('jawaban_pesertas')
                ->where('id', $request->jawaban_id)
                ->select('id','banksoal_id','soal_id','jawab','esay','ragu_ragu')
                ->first();

//            $cache->cache($key, $find);
//        }

        if (!$find) {
            return SendResponse::badRequest('Kami tidak dapat menemukan jawaban anda');
        }

        if(!isset($request->ragu_ragu)) {
            return response()->json(['data' => $find,'index' => $request->index]);
        }

        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $ujian = $ujianService->onProgressToday($peserta->id);

        if (!$ujian) {
            return SendResponse::badRequest('Kami tidak dapat menemukan ujian yang sedang anda kamu kerjakan, mungkin jadawl ini sedang tidak aktif. silakan logout lalu hubungi administrator.');
        }

        // update sisa waktu ujian
        $ujianService->updateReminingTime($ujian);

        try {
            DB::table('jawaban_pesertas')
                ->where('id', $find->id)
                ->update([
                    'ragu_ragu' => $request->ragu_ragu
                ]);
        } catch (\Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }

        return response()->json(['data' => $find,'index' => $request->index]);
    }

    /**
     * @Route(path="api/v2/ujian/selesai", methods={"GET"})
     *
     * Selesaikan ujian
     *
     * @param ShellreanDev\Services\UjianService $ujianService
     * @return Illuminate\Http\Response
     * @author shellrean <wandnak17@gmail.com>
     */
    public function selesai(UjianService $ujianService)
    {
        $peserta = request()->get('peserta-auth');

        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $ujian = $ujianService->onProgressToday($peserta->id);

        if (!$ujian) {
            return SendResponse::badRequest('Anda tidak sedang mengerjakan ujian apapun. silakan logout, laporkan perihal ini kepada administrator');
        }

        // Cek apakah hasil ujian pernah di generate sebelumnya
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
                        'status_ujian'  => 1
                    ]);

            } catch (\Exception $e) {
                return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
            }
            return SendResponse::badRequest('Ujian ini telah diselesaikan. silakan logout, laporkan perihal ini kepada andministrator');
        }

        // ambil hanya banksoal untuk
        // jawaban peserta pertama
        $jawaban = DB::table('jawaban_pesertas')
            ->where([
                'jadwal_id'     => $ujian->jadwal_id,
                'peserta_id'    => $peserta->id
            ])
            ->select('banksoal_id')
            ->first();

        if (!$jawaban) {
            return SendResponse::badRequest('Anda tidak sedang mengerjakan ujian apapun. silakan logout, laporkan perihal ini kepada administrator');
        }

        try {
            DB::beginTransaction();

            $ujianService->finishing($jawaban->banksoal_id, $ujian->jadwal_id, $peserta->id);

            DB::table('siswa_ujians')
                ->where('id', $ujian->id)
                ->update([
                    'status_ujian'  => 1,
                ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
        return SendResponse::accept('ujian berhasil diselesaikan');
    }
}

