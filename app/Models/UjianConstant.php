<?php

namespace App\Models;

class UjianConstant
{
    public const STATUS_FINISHED = 1;
    public const STATUS_PROGRESS = 3;
    public const STATUS_STANDBY = 0;
    public const STATUS_BEFORE_START = 2;

    public const NO_CURRENT_UJIAN_EXIST = "Kami tidak dapat mengambil ujian untuk kamu, kamu tidak sedang mengikuti ujian apapun. silakan logout lalu login kembali";
    public const NO_WORKING_UJIAN_FOUND = "'Kami tidak dapat menemukan ujian yang sedang anda kamu kerjakan, mungkin jadawl ini sedang tidak aktif. silakan logout lalu hubungi administrator.'";
    public const NO_BANKSOAL_FOR_YOU = "Kamu tidak mendapat banksoal yang sesuai, silakan logout lalu hubungi administrator";
    public const NO_WORKING_ANSWER_FOUND = "Kami tidak dapat menemukan data dari jawaban kamu";
    public const WARN_UJIAN_HAS_FINISHED_BEFORE = "Ujian ini telah diselesaikan. silakan logout, laporkan perihal ini kepada andministrator";
    public const MINUMUM_TEST_INVALID = "Waktu minimal pengerjaan belum terpenuhi, kerjakan soal anda minimal";
}
