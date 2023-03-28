<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pilihan ganda service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class PilihanGandaService implements TipeSoalInterface
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
        $max_soal = $banksoal->jumlah_soal;
        $setting = json_decode($jadwal->setting, true);

        if ($max_soal > 0) {
            $pg = DB::table('soals')->where([
                'banksoal_id' => $banksoal->id,
                'tipe_soal' => SoalConstant::TIPE_PG
            ]);
            if ($setting['acak_soal'] == "1") {
                $pg = $pg->inRandomOrder();
            } else {
                $pg = $pg->orderBy('created_at');
            }
            # Ambil soal sebanyak maximum
            $pg = $pg->take($max_soal)->get();

            $soal_pg = [];
            foreach ($pg as $k => $item) {
                array_push($soal_pg, [
                    'id' => Str::uuid()->toString(),
                    'peserta_id' => $peserta->id,
                    'banksoal_id' => $banksoal->id,
                    'soal_id' => $item->id,
                    'jawab' => 0,
                    'iscorrect' => 0,
                    'jadwal_id' => $jadwal->id,
                    'ragu_ragu' => 0,
                    'esay' => ''
                ]);
            }

            return $soal_pg;
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
        $kj = DB::table('jawaban_soals')
                ->where('id', $request->jawab)
                ->select('correct');

        # Cache layer, system need more helper
        if (config('exo.enable_cache')) {
            $cacheKeyConsolidate = "jawaban_soal_pilihan_ganda_1297161284_".$request->jawab;
            if(Cache::has($cacheKeyConsolidate)) {

                # Get Cache if exist
                $kj = Cache::get($cacheKeyConsolidate);
            } else {
                $kj = $kj->first();

                # Make sure the cache not null when stored to memcache
                if($kj) {
                    Cache::put($cacheKeyConsolidate, $kj, 60);
                }
            }
        } else {
            $kj = $kj->first();
        }
        
        if(!$kj) {
            return SendResponse::acceptCustom([
                'data' => [
                    'jawab' => $jawaban_peserta->jawab
                ],
                'index' => $request->index
            ]);
        }

        try {
            $data_update = [
                'jawab'         => $request->jawab,
                'iscorrect'     => $kj->correct,
            ];
            if (!$jawaban_peserta->answered) {
                $data_update['answered'] = true;
            }
            DB::table('jawaban_pesertas')
                ->where('id', $jawaban_peserta->id)
                ->update($data_update);
                
            $jawaban_peserta->jawab = $request->jawab;

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
