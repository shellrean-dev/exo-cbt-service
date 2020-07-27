<?php

/**
 * -----------------------------------------------
 * Ujian Service
 * @since 1.4 Esresso
 * @author shellrean <wandinak17@gmail.com>
 * -----------------------------------------------
 */
namespace App\Services;

use App\Ujian;
use App\Jadwal;
use App\Peserta;
use App\Banksoal;
use Carbon\Carbon;
use App\SiswaUjian;
use App\HasilUjian;
use App\JawabanPeserta;

class UjianService
{
    /**
     * Create new ujian
     * @param  array $data [description]
     * @return array       [description]
     */
	public static function createNew(array $data)
    {
        try {
            Ujian::create($data);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
        return ['success' => true, 'message' => 'Success to create new ujian'];
    }

    /**
     * [getBanksoalPeserta description]
     * @param  Banksoal $banksoal [description]
     * @param  array    $peserta  [description]
     * @return [type]             [description]
     */
    public static function getBanksoalPeserta(Banksoal $banksoal, Peserta $peserta)
    {
        $banksoal_id = '';
        try {
            if($banksoal->matpel->agama_id != 0) {
                if($banksoal->matpel->agama_id == $peserta['agama_id']) {
                    $banksoal_id = $banksoal->id;
                }
            } else {
                if(is_array($banksoal->matpel->jurusan_id)) {
                    foreach ($banksoal->matpel->jurusan_id as $d) {
                        if($d == $peserta['jurusan_id']) {
                            $banksoal_id = $banksoal->id;
                        }
                    }
                } else {
                    if($banksoal->matpel->jurusan_id == 0) {
                        $banksoal_id = $banksoal->id;
                    }
                }
            }   
        } catch (\Exception $e) {
            return ['success' => false, 'messge' => $e->getMessage()];
        }

        return ['success' => true, 'data' => $banksoal_id];
    }

    /**
     * [getJawabanPeserta description]
     * @param  [type] $jadwal_id  [description]
     * @param  [type] $peserta_id [description]
     * @return [type]             [description]
     */
    public static function getJawabanPeserta($jadwal_id, $peserta_id)
    {
        $find = JawabanPeserta::with([
          'soal' => function($q) {
            $q->select('id','banksoal_id','pertanyaan','tipe_soal','audio','direction'); 
        },'soal.jawabans' => function($q) {
            $q->select('id','soal_id','text_jawaban');
            if(true) {
                $q->inRandomOrder();
            }
        }
        ])->where([
            'peserta_id'    => $peserta_id,
            'jadwal_id'     => $jadwal_id,
        ])
        ->select('id','banksoal_id','soal_id','jawab','esay','ragu_ragu')
        ->get()
        ->makeHidden('similiar');
        return $find;
    }

    /**
     * [finishingUjian description]
     * @param  [type] $jadwal_id  [description]
     * @param  [type] $peserta_id [description]
     * @return [type]             [description]
     */
    public static function finishingUjian($banksoal_id, $jadwal_id, $peserta_id)
    {
        try { 
            $salah = JawabanPeserta::where([
                'iscorrect'     => 0,
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id,
            ])
            ->whereHas('soal', function($query) {
                $query->where('tipe_soal','!=', '2');
            })
            ->count();

            $benar = JawabanPeserta::where([
                'iscorrect'     => 1,
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id
            ])
            ->count();

            $jml = JawabanPeserta::where([
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id
            ])
            ->whereHas('soal', function($query) {
                $query->where('tipe_soal','!=', '2');
            })
            ->count();

            $null = JawabanPeserta::where([
                'jawab'     => 0,
                'jadwal_id'     => $jadwal_id, 
                'peserta_id'    => $peserta_id,
            ])
            ->whereHas('soal', function($query) {
                $query->where('tipe_soal','!=', '2');
            })
            ->count();

            $hasil = ($benar/$jml)*70;

            HasilUjian::create([
                'banksoal_id'     => $banksoal_id,
                'peserta_id'      => $peserta_id,
                'jadwal_id'       => $jadwal_id,
                'jumlah_salah'    => $salah,
                'jumlah_benar'    => $benar,
                'tidak_diisi'     => $null,
                'hasil'           => $hasil,
                'point_esay'      => 0
            ]);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
        return ['success' => true, 'message' => 'success to store ujian siswa'];
    }

    /**
     * [kurangiSisaWaktu description]
     * @param  SiswaUjian $siswaUjian [description]
     * @return [type]                 [description]
     */
    public static function kurangiSisaWaktu(SiswaUjian $siswaUjian)
    {
        $deUjian = Jadwal::find($siswaUjian->jadwal_id);
        $start = Carbon::createFromFormat('H:i:s', $siswaUjian->mulai_ujian);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);
        $siswaUjian->sisa_waktu = $deUjian->lama-$diff_in_minutes;
        $siswaUjian->save();
    }

    /**
     * [getUjianSiswaBelumSelesai description]
     * @param  SiswaUjian $siswaUjian [description]
     * @return [type]                 [description]
     */
    public function getUjianSiswaBelumSelesai($peserta_id)
    {
        $data = SiswaUjian::where(function($query) use($peserta_id) {
            $query->where('peserta_id', $peserta_id)
            ->where('status_ujian','=',3);
        })->first();

        return $data;
    }
}