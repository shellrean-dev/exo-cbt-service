<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BenarSalahService
{
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
}
