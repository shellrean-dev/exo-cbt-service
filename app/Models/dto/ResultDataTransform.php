<?php

namespace App\Models\dto;

use Carbon\Carbon;

class ResultDataTransform
{
    public static function resultExam($item)
    {
        if(!$item->mulai_ujian || !$item->selesai_ujian) {
            $waktu_pengerjaan = "Tidak dapat dihitung";
        } else {
            $waktu_pengerjaan = Carbon::createFromFormat('H:i:s', $item->mulai_ujian)->diffInMinutes(Carbon::createFromFormat('H:i:s', $item->selesai_ujian));
            $waktu_pengerjaan = $waktu_pengerjaan. ' menit';
        }
        
        return (object) [
            'id'    => $item->id,
            'jumlah_benar' => $item->jumlah_benar,
            'jumlah_benar_benar_salah' => $item->jumlah_benar_benar_salah,
            'jumlah_benar_complek' => $item->jumlah_benar_complek,
            'jumlah_benar_isian_singkat' => $item->jumlah_benar_isian_singkat,
            'jumlah_benar_listening' => $item->jumlah_benar_listening,
            'jumlah_benar_mengurutkan' => $item->jumlah_benar_mengurutkan,
            'jumlah_benar_menjodohkan' => $item->jumlah_benar_menjodohkan,
            'jumlah_salah' => $item->jumlah_salah,
            'jumlah_salah_benar_salah' => $item->jumlah_salah_benar_salah,
            'jumlah_salah_complek' => $item->jumlah_salah_complek,
            'jumlah_salah_isian_singkat' => $item->jumlah_salah_isian_singkat,
            'jumlah_salah_listening' => $item->jumlah_salah_listening,
            'jumlah_salah_mengurutkan' => $item->jumlah_salah_mengurutkan,
            'jumlah_salah_menjodohkan' => $item->jumlah_salah_menjodohkan,
            'point_esay' => $item->point_esay,
            'point_setuju_tidak' => $item->point_setuju_tidak,
            'tidak_diisi' => $item->tidak_diisi,
            'hasil' => $item->hasil,
            'ujian' => (object) [
                'mulai' => $item->mulai_ujian,
                'selesai' => $item->selesai_ujian,
                'pengerjaan' => $waktu_pengerjaan
            ],
            'peserta' => (object) [
                'nama' => $item->peserta_nama,
                'no_ujian' => $item->peserta_no_ujian
            ]
        ];
    }

    public static function resultUjianDetail($item)
    {
        return (object) [
            'banksoal_id' => $item->banksoal_id,
            'esay' => $item->esay,
            'esay_result' => $item->esay_result,
            'id' => $item->id,
            'iscorrect' => $item->iscorrect,
            'jadwal_id' => $item->jawab_id,
            'mengurutkan' => json_decode($item->mengurutkan),
            'setuju_tidak' => json_decode($item->setuju_tidak),
            'benar_salah' => json_decode($item->benar_salah),
            'jawab' => $item->jawab,
            'jawab_complex' => $item->jawab_complex,
            'peserta_nama'  => $item->peserta->nama,
            'peserta_no_ujian' => $item->peserta->no_ujian,
            'peserta_id' => $item->peserta_id,
            'ragu_ragu' => $item->ragu_ragu,
            'similiar' => $item->similiar,
            'soal' => $item->soal,
            'soal_id' => $item->soal_id,
            'updated_at' => $item->updated_at,
        ];
    }
}
