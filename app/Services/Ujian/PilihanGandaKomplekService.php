<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PilihanGandaKomplekService
{
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
}
