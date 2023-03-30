<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use App\Soal;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pilihan ganda komplek service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class PilihanGandaKomplekService implements TipeSoalInterface
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
        $max_complex = $banksoal->jumlah_soal_ganda_kompleks;
        $setting = json_decode($jadwal->setting, true);

        if ($max_complex > 0) {
            $complex = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_PG_KOMPLEK
            ]);

            # Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $complex = $complex->inRandomOrder();
            } else {
                $complex = $complex->orderBy('created_at');
            }

            # Ambil soal sebanyak maximum
            $complex = $complex->take($max_complex)->get();

            $soal_complex = [];
            foreach($complex as $k => $item) {
                array_push($soal_complex, [
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

            return $soal_complex;
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
        $soal_complex = Soal::with(['jawabans' => function($query) {
            $query->where('correct', 1);
        }])->where("id", $jawaban_peserta->soal_id);

        if(config('exo.enable_cache')) {
            $cacheKeyConsolidate = "soals_TB5I8Z25NZ_".$jawaban_peserta->soal_id;
            if(Cache::has($cacheKeyConsolidate)) {
                $soal_complex = Cache::get($cacheKeyConsolidate);

            } else {
                $soal_complex = $soal_complex->first();
                if($soal_complex) {
                    Cache::put($cacheKeyConsolidate, $soal_complex, 60);
                    
                }
            }
        } else {
            $soal_complex = $soal_complex->first();
        }

        if ($soal_complex) {
            $array = $soal_complex->jawabans->map(function($item){
                return $item->id;
            })->toArray();

            $correct = 0;
            $complex = array_diff( $request->jawab_complex, [0] );
            if (array_diff($array,$complex) == array_diff($complex,$array)) {
                $correct = 1;
            }
            $jawaban_peserta->iscorrect = $correct;
        }

        try {
            $data_update = [
                'jawab_complex' => json_encode($request->jawab_complex),
                'iscorrect'     => $jawaban_peserta->iscorrect
            ];
            if (!$jawaban_peserta->answered) {
                $data_update['answered'] = true;
            }
            DB::table('jawaban_pesertas')
                ->where('id', $jawaban_peserta->id)
                ->update($data_update);

            $jawaban_peserta->jawab_complex = json_encode($request->jawab_complex);

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
