<?php

namespace App\Utils;

use App\Models\SoalConstant;

/**
 * soal util
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.1 <ristretto>
 */
class SoalUtil
{
    /**
     * get tipe soal text
     *
     * @author shellrean <wandinak17@gmail.com>
     * @since 3.0.1 <ristretto>
     * @param int $tipe_soal
     * @return string
     */
    public static function tipeSoalTextOf(int $tipe_soal)
    {
        switch ($tipe_soal) {
            case SoalConstant::TIPE_PG:
                return "PG";
            case SoalConstant::TIPE_ESAY:
                return "ESAY";
            case SoalConstant::TIPE_LISTENING:
                return "LISTENING";
            case SoalConstant::TIPE_PG_KOMPLEK:
                return "PG_KOMPLEK";
            case SoalConstant::TIPE_MENJODOHKAN:
                return "MENJODOHKAN";
            case SoalConstant::TIPE_ISIAN_SINGKAT:
                return "ISIAN_SINGKAT";
            case SoalConstant::TIPE_MENGURUTKAN:
                return "MENGURUTKAN";
            case SoalConstant::TIPE_BENAR_SALAH:
                return "BENAR_SALAH";
            case SoalConstant::TIPE_SETUJU_TIDAK:
                return "SETUJU_TIDAK";
            default:
                return "UNKNOWN";
        }
    }
}
