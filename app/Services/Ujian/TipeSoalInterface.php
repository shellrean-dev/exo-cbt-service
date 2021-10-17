<?php

namespace App\Services\Ujian;

interface TipeSoalInterface
{
    /**
     * ambil soal dari tipe soal
     *
     * @param $peserta
     * @param $banksoal
     * @param $jadwal
     * @return mixed
     */
    public static function getSoal($peserta, $banksoal, $jadwal);

    /**
     * set jawaban untuk tipe soal
     *
     * @param $request
     * @param $jawaban_peserta
     * @return mixed
     */
    public static function setJawab($request, $jawaban_peserta);
}
