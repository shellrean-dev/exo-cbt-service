<?php

namespace App\Services\Ujian;

use App\Actions\SendResponse;
use App\Models\SoalConstant;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Soal tipe isian singkat service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.0 <ristretto>
 * @year 2021
 */
class IsianSingkatService implements TipeSoalInterface
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
        $max_isian_singkat = $banksoal->jumlah_isian_singkat;

        if ($max_isian_singkat > 0) {
            $isian_singkat = DB::table('soals')->where([
                'banksoal_id'   => $banksoal->id,
                'tipe_soal'     => SoalConstant::TIPE_ISIAN_SINGKAT
            ]);

            # Acak soal bila di set
            if($setting['acak_soal'] == "1") {
                $isian_singkat = $isian_singkat->inRandomOrder();
            } else {
                $isian_singkat = $isian_singkat->orderBy('created_at');
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

    /**
     * @param $request
     * @param $jawaban_peserta
     * @return Response
     */
    public static function setJawab($request, $jawaban_peserta)
    {
        $jwb_soals = DB::table('jawaban_soals')
            ->where('soal_id', $jawaban_peserta->soal_id)
            ->select(['id', 'text_jawaban'])
            ->get();
        $soal = DB::table('soals')
            ->where('id', $jawaban_peserta->soal_id)
            ->select('id', 'case_sensitive')
            ->first();

        foreach($jwb_soals as $jwb) {
            $jwb_strip = strip_tags($jwb->text_jawaban);
            $isian_siswa = $request->isian;
            if ($soal->case_sensitive == '0') {
                $jwb_strip = strtoupper($jwb_strip);
                $isian_siswa = strtoupper($isian_siswa);
            }
            if (trim($jwb_strip) == trim($isian_siswa)) {
                $jawaban_peserta->iscorrect = 1;
                break;
            }
            $jawaban_peserta->iscorrect = 0;
        }

        try {
            $data_update = [
                'iscorrect' => $jawaban_peserta->iscorrect,
                'esay'      => $request->isian
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
