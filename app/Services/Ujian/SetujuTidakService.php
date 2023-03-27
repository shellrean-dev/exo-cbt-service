<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Setuju tidak service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class SetujuTidakService implements TipeSoalInterface
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
        $max_setuju_tidak = $banksoal->jumlah_setuju_tidak;

        if ($max_setuju_tidak > 0) {
            $setuju_tidak = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_SETUJU_TIDAK
            ]);
            if($setting['acak_soal'] == "1") {
                $setuju_tidak = $setuju_tidak->inRandomOrder();
            } else {
                $setuju_tidak = $setuju_tidak->orderBy('created_at');
            }

            $setuju_tidak = $setuju_tidak->take($max_setuju_tidak)->get();

            $soal_setuju_tidak= [];
            foreach ($setuju_tidak as $k => $item) {
                array_push($soal_setuju_tidak, [
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
            return $soal_setuju_tidak;
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
        try {
            $data_update = [
                'setuju_tidak'  => $request->setuju_tidak
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
