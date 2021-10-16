<?php

namespace App\Models\dto;

class ResultDataTransform
{
    public static function resultExam($item) {
        return [
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
            'peserta' => [
                'nama' => $item->peserta_nama,
                'no_ujian' => $item->peserta_no_ujian
            ]
        ];
    }
}
