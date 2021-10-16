<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IsianSingkatService
{
    public static function getSoal($peserta, $banksoal, $jadwal)
    {
        # Setup
        $setting = json_decode($jadwal->setting, true);
        $max_isian_singkat = $banksoal->jumlah_isian_singkat;

        if ($max_isian_singkat > 0) {
            $isian_singkat = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_ISIAN_SINGKAT
            ]);

            # Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $isian_singkat = $isian_singkat->inRandomOrder();
            }

            # Ambil soal sebanyak maximum
            $isian_singkat = $isian_singkat->take($max_isian_singkat)->get();

            $soal_isian_singkat = [];
            foreach($isian_singkat as $k => $item) {
                array_push($soal_isian_singkat, [
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

            return $soal_isian_singkat;
        }

        return [];
    }
}
