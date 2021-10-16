<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EsayService
{
    public static function getSoal($peserta, $banksoal, $jadwal)
    {
        # Setup
        $setting = json_decode($jadwal->setting, true);
        $max_esay = $banksoal->jumlah_soal_esay;

        if ($max_esay > 0) {
            $esay = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_ESAY
            ]);

            # Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $esay = $esay->inRandomOrder();
            }

            # Ambil soal sebanyak maximum
            $esay = $esay->take($max_esay)->get();

            $soal_esay = [];
            foreach($esay as $k => $item) {
                array_push($soal_esay, [
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

            return $soal_esay;
        }
        return [];
    }
}
