<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Soal tipe benar salah service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class BenarSalahService
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
        $max_benar_salah = $banksoal->jumlah_benar_salah;

        if ($max_benar_salah > 0) {
            $benar_salah = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_BENAR_SALAH
            ]);
            if($setting['acak_soal'] == "1") {
                $benar_salah = $benar_salah->inRandomOrder();
            }
            $benar_salah = $benar_salah->take($max_benar_salah)->get();

            $soal_benar_salah= [];
            foreach ($benar_salah as $k => $item) {
                array_push($soal_benar_salah, [
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

            return $soal_benar_salah;
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
        $soal_benar_salah = DB::table('soals as s')
            ->join('jawaban_soals as j', 'j.soal_id', '=','s.id')
            ->select('j.id as jawaban_id', 'correct')
            ->where('s.id', $jawaban_peserta->soal_id)
            ->get();

        $count_corect = 0;
        foreach ($request->benar_salah as $k => $v) {
            $seachSoal = $soal_benar_salah->where('jawaban_id', $k)->first();
            if ($seachSoal && ($seachSoal->correct == $v)) {
                $count_corect += 1;
            }
        }

        $jawaban_peserta->iscorrect = 0;
        if (count($soal_benar_salah) == $count_corect) {
            $jawaban_peserta->iscorrect = 1;
        }

        try {
            $data_update = [
                'benar_salah' => json_encode($request->benar_salah),
                'iscorrect'     => $jawaban_peserta->iscorrect,
            ];
            if (!$jawaban_peserta->answered) {
                $data_update['answered'] = true;
            }
            DB::table('jawaban_pesertas')
                ->where('id', $jawaban_peserta->id)
                ->update($data_update);
            $jawaban_peserta->benar_salah = json_encode($request->benar_salah);

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
