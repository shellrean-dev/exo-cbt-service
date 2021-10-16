<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MengurutkanService
{
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
}
