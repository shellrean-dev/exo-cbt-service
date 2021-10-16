<?php

namespace App\Services\Ujian;

use App\Models\SoalConstant;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetujuTidakService
{
    public static function getSoal($peserta, $banksoal, $jadwal)
    {
        # Setup
        $settiing = json_decode($jadwal, true);
        $max_setuju_tidak = $banksoal->jumlah_setuju_tidak;

        if ($max_setuju_tidak > 0) {
            $setuju_tidak = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_SETUJU_TIDAK
            ]);
            if($setting['acak_soal'] == "1") {
                $setuju_tidak = $setuju_tidak->inRandomOrder();
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
}
