<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Soal tipe listening service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class ListeningService implements TipeSoalInterface
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
        $max_listening = $banksoal->jumlah_soal_listening;
        $setting = json_decode($jadwal->setting, true);

        if ($max_listening > 0) {
            $listening = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_LISTENING
            ])->orderBy('created_at');

            # Ambil soal sebanyak maximum
            $listening = $listening->take($max_listening)->get();

            $soal_listening = [];
            foreach($listening as $k => $item) {
                array_push($soal_listening, [
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

            return $soal_listening;
        }

        return [];
    }

    /**
     * @param $request
     * @param $jawaban_peserta
     * @return void
     */
    public static function setJawab($request, $jawaban_peserta)
    {
    }
}
