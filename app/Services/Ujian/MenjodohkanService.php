<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenjodohkanService
{
    public static function getSoal($peserta, $banksoal, $jadwal)
    {
        # Setup
        $setting = json_decode($jadwal->setting, true);
        $max_menjodohkan = $banksoal->jumlah_menjodohkan;

        if ($max_menjodohkan > 0) {
            $menjodohkan = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_MENJODOHKAN
            ]);
            if($setting['acak_soal'] == "1") {
                $menjodohkan = $menjodohkan->inRandomOrder();
            }
            $menjodohkan = $menjodohkan->take($max_menjodohkan)->get();

            $soal_menjodohkan = [];
            foreach ($menjodohkan as $k => $item) {
                array_push($soal_menjodohkan, [
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

            return $soal_menjodohkan;
        }
        return [];
    }
}
