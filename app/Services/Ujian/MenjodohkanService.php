<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Menjodohkan service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class MenjodohkanService implements TipeSoalInterface
{
    /**
     * @param $peserta
     * @param $banksoal
     * @param $jadwal
     * @return array
     */
    public static function getSoal($peserta, $banksoal, $jadwal)
    {
        # Setup
        $setting = json_decode($jadwal->setting, true);
        $max_menjodohkan = $banksoal->jumlah_menjodohkan;

        if ($max_menjodohkan > 0) {
            $menjodohkan = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_MENJODOHKAN
            ]);
            if($setting['acak_soal'] == "1") {
                $menjodohkan = $menjodohkan->inRandomOrder();
            } else {
                $menjodohkan = $menjodohkan->orderBy('created_at');
            }
            $menjodohkan = $menjodohkan->take($max_menjodohkan)->get();

            $soal_menjodohkan = [];
            foreach ($menjodohkan as $k => $item) {
                array_push($soal_menjodohkan, [
                    'id'            => Str::uuid()->toString(),
                    'peserta_id'    => $peserta->id,
                    'banksoal_id'   => $banksoal->id,
                    'soal_id'       => $item->id,
                    'jawab'         => 0,
                    'iscorrect'     => 0,
                    'jadwal_id'     => $jadwal->id,
                    'ragu_ragu'     => 0,
                    'esay'          => ''
                ]);
            }

            return $soal_menjodohkan;
        }
        return [];
    }

    /**
     * @param $request
     * @param $jawaban_peserta
     * @return Response
     */
    public static function setJawab($request, $jawaban_peserta)
    {
        $jwb_soals = DB::table('jawaban_soals')
            ->where('soal_id', $jawaban_peserta->soal_id);

        if(config('exo.enable_cache')) {
            $cacheKeyConsolidate = "jawaban_soals_3LHVWCOYFM_".$jawaban_peserta->soal_id;
            if(Cache::has($cacheKeyConsolidate)) {
                $jwb_soals = Cache::get($cacheKeyConsolidate, new Collection());

            } else {
                $jwb_soals = $jwb_soals->get();
                if($cacheKeyConsolidate) {
                    Cache::put($cacheKeyConsolidate, $jwb_soals, 60);

                }

            }
        } else {
            $jwb_soals = $jwb_soals->get();
        }


        $menjodohkan_correct = $jwb_soals->map(function($item) {
            $obj = json_decode($item->text_jawaban, true);
            return [$obj['a']['id'], $obj['b']['id']];
        });
        $count_corect = 0;
        foreach ($request->menjodohkan as $result) {
            foreach ($menjodohkan_correct as $item) {
                if (($result[0] == $item[0]) && ($result[1] == $item[1])) {
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
            $data_update = [
                'iscorrect'     => $result_menjodohkan_correct,
                'menjodohkan'   => json_encode($request->menjodohkan)
            ];
            if (!$jawaban_peserta->answered) {
                $data_update['answered'] = true;
            }
            DB::table('jawaban_pesertas')
                ->where('id', $jawaban_peserta->id)
                ->update($data_update);

            return SendResponse::acceptCustom([
                'data' => [
                    'jawab' => $jawaban_peserta->jawab,
                ],
                'index' => $request->index
            ]);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
    }
}
