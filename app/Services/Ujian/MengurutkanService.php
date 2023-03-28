<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Soal tipe mengurutkan service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class MengurutkanService implements TipeSoalInterface
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
        $max_mengurutkan = $banksoal->jumlah_mengurutkan;

        if ($max_mengurutkan > 0) {
            $mengurutkan = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_MENGURUTKAN
            ]);
            if($setting['acak_soal'] == "1") {
                $mengurutkan = $mengurutkan->inRandomOrder();
            } else {
                $mengurutkan = $mengurutkan->orderBy('created_at');
            }
            $mengurutkan = $mengurutkan->take($max_mengurutkan)->get();

            $soal_mengurutkan = [];
            foreach ($mengurutkan as $k => $item) {
                array_push($soal_mengurutkan, [
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
            return $soal_mengurutkan;
        }
        return [];
    }

    /**
     * @param $request
     * @param $jawaban_peserta
     * @return \Illuminate\Http\Response
     */
    public static function setJawab($request, $jawaban_peserta)
    {
        $jwb_soals = DB::table('jawaban_soals')
            ->where('soal_id', $jawaban_peserta->soal_id)
            ->orderBy('created_at');

        if(config('exo.enable_cache')) {
            $cacheKeyConsolidate = "jawaban_soals_RMA2R25GAY_".$jawaban_peserta->soal_id;
            if(Cache::has($cacheKeyConsolidate)) {
                $jwb_soals = Cache::get($cacheKeyConsolidate, new Collection());

            } else {
                $jwb_soals = $jwb_soals->get();
                if($jwb_soals) {
                    Cache::put($cacheKeyConsolidate, $jwb_soals, 60);
                }
            }
        } else {
            $jwb_soals = $jwb_soals->get();
        }

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
            $data_update = [
                'iscorrect' => $result_mengurutkan_correct,
                'mengurutkan' => json_encode($request->mengurutkan)
            ];
            if (!$jawaban_peserta->answered) {
                $data_update['answered'] = true;
            }
            DB::table('jawaban_pesertas')
                ->where('id', $jawaban_peserta->id)
                ->update($data_update);

            return SendResponse::acceptCustom([
                'data' => [
                    'jawab' => $jawaban_peserta->jawab
                ],
                'index' => $request->index
            ]);
        } catch (Exception $e) {
            return SendResponse::internalServerError('Terjadi kesalahan 500. '.$e->getMessage());
        }
    }
}
