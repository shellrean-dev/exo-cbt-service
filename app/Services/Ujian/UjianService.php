<?php declare(strict_types=1);

namespace ShellreanDev\Services\Ujian;

use App\Models\CacheConstant;
use App\Models\SoalConstant;
use App\Models\UjianConstant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Exception;
use Carbon\Carbon;

use App\Banksoal;
use App\Peserta;
use App\JawabanPeserta;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;
use ShellreanDev\Services\Jadwal\JadwalService;

/**
 * Ujian Service
 *
 * @since 3.0.0 <ristretto>
 * @author shellrean <wandinak17@gmail.com>
 */
final class UjianService extends AbstractService
{
    /**
     * Jadwal Service
     * @var JadwalService
     */
    protected JadwalService $jadwalService;

    /**
     * Inject dependency
     *
     * @param CacheHandler $cache
     * @param JadwalService $jadwalService
     * @since 3.0.0 <ristretto>
     */
    public function __construct(CacheHandler $cache, JadwalService $jadwalService)
    {
        $this->cache = $cache;
        $this->jadwalService = $jadwalService;
    }

    /**
     * Get ujian on working today
     *
     * @param string $student_id
     * @return Model|Builder|object|null
     * @since 3.0.0 <ristretto>
     */
    public function onWorkingToday(string $student_id)
    {
        # ambil ujian yang aktif hari ini
        $jadwals = $this->jadwalService->activeToday();

        $jadwal_ids = $jadwals->pluck('id')->toArray();

        # ambil data siswa ujian
        # yang sedang dikerjakan pada hari ini
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $student_id)
            ->whereIn('status_ujian', [UjianConstant::STATUS_STANDBY,UjianConstant::STATUS_PROGRESS])
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->select(['jadwal_id', 'status_ujian'])
            ->orderBy('created_at')
            ->orderByDesc('status_ujian')
            ->first();

        return $data;
    }

    /**
     * Get ujian on standby today
     *
     * @param string $student_id
     * @return Model|Builder|object|null
     * @since 3.0.0 <ristretto>
     */
    public function onStandbyToday(string $student_id)
    {
        # ambil ujian yang aktif hari ini
        $jadwals = $this->jadwalService->activeToday();

        $jadwal_ids = $jadwals->pluck('id')->toArray();

        # ambil data siswa ujian
        # yang sudah dijalankan pada hari ini
        # tetapi belum dimulai
        # yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $data = DB::table('siswa_ujians')
            ->where('peserta_id', $student_id)
            ->where('status_ujian', UjianConstant::STATUS_STANDBY)
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->first();

        return $data;
    }

    /**
     * Get ujian on progress today
     *
     * @param string $student_id
     * @return Model|Builder|object|null
     * @since 3.0.0 <ristretto>
     */
    public function onProgressToday(string $student_id)
    {
        # ambil ujian yang aktif hari ini
        $jadwals = $this->jadwalService->activeToday();

        $jadwal_ids = $jadwals->pluck('id')->toArray();
        $query = DB::table('siswa_ujians')
            ->where('peserta_id', $student_id)
            ->where('status_ujian', '=', UjianConstant::STATUS_PROGRESS)
            ->whereIn('jadwal_id', $jadwal_ids)
            ->whereDate('created_at', now()->format('Y-m-d'));

        if (config('exo.enable_cache')) {
            $is_cached = $this->cache->isCached(CacheConstant::KEY_UJIAN_ON_PROGRESS,  $student_id . implode('-', $jadwal_ids));
            if ($is_cached) {
                $data = $this->cache->getItem(CacheConstant::KEY_UJIAN_ON_PROGRESS,  $student_id . implode('-', $jadwal_ids));
            } else {
                $data = $query->first();
                if ($data) {
                    $this->cache->cache(CacheConstant::KEY_UJIAN_ON_PROGRESS,  $student_id . implode('-', $jadwal_ids), $data);
                }
            }
        } else {
            $data = $query->first();
        }

        return $data;
    }

    /**
     * Get ujian on progress today
     *
     * @param string $student_id
     * @return Model|Builder|object|null
     * @since 3.0.0 <ristretto>
     */
    public function deactivateCacheOnProgressToday(string $student_id)
    {
        if (config('exo.enable_cache')) {
            # ambil ujian yang aktif hari ini
            $jadwals = $this->jadwalService->activeToday();
            $jadwal_ids = $jadwals->pluck('id')->toArray();

            $this->cache->deleteItem(CacheConstant::KEY_UJIAN_ON_PROGRESS,$student_id . implode('-', $jadwal_ids));
        }
    }

    /**
     * Is peserta's banksoal
     *
     * @param Banksoal $banksoal
     * @param Peserta $peserta
     * @return string
     * @since 3.0.0 <ristretto>
     */
    public function checkPesertaBanksoal($banksoal, Peserta $peserta)
    {
        $banksoal_id = '';
        try {
            if ($banksoal->agama_id != '0') {
                if ($banksoal->agama_id == $peserta->agama_id) {
                    $banksoal_id = $banksoal->id;
                }
            } else {
                $jurusans = ($banksoal->jurusan_id == '0' || $banksoal->jurusan_id == '')
                    ? 0
                    : json_decode($banksoal->jurusan_id, true);
                if(is_array($jurusans) && $jurusans != null) {
                    foreach ($jurusans as $d) {
                        if($d == $peserta['jurusan_id']) {
                            $banksoal_id = $banksoal->id;
                        }
                    }
                } else {
                    if($banksoal->jurusan_id == 0) {
                        $banksoal_id = $banksoal->id;
                    }
                }
            }
        } catch (Exception $e) {
            return $banksoal_id;
        }
        return $banksoal_id;
    }

    /**
     * Get peserta's answer
     *
     * @param string $jadwal_id
     * @param string $peserta_id
     * @param string $acak_opsi
     * @return array
     * @since 3.0.0 <ristretto>
     */
    public function pesertaAnswers(string $jadwal_id, string $peserta_id, string $acak_opsi)
    {
        $find = DB::table('jawaban_pesertas')
            ->where([
                'peserta_id'    => $peserta_id,
                'jadwal_id'     => $jadwal_id,
            ])
            ->select([
                'id',
                'banksoal_id',
                'soal_id',
                'jawab',
                'esay',
                'jawab_complex',
                'ragu_ragu',
                'menjodohkan',
                'mengurutkan',
                'benar_salah',
                'setuju_tidak',
                'answered'])
            ->orderBy('created_at', 'ASC')
            ->get();

        $soals = DB::table('soals')
            ->whereIn('id', $find->pluck('soal_id')->toArray())
            ->select([
                'id',
                'banksoal_id',
                'pertanyaan',
                'tipe_soal',
                'audio',
                'direction',
                'layout'
            ])
            ->get();
        $soal_jawabans = DB::table('jawaban_soals')
            ->whereIn('soal_id', $soals->pluck('id')->toArray());
        if ($acak_opsi == '1') {
            $soal_jawabans = $soal_jawabans->inRandomOrder();
        } else {
            $soal_jawabans = $soal_jawabans->orderBy('created_at');
        }

        $soal_jawabans = $soal_jawabans->get();
        $soal_jawabans_indexeds = $soal_jawabans->groupBy('soal_id');

        $soals = $soals->map(function ($item) use ($soal_jawabans_indexeds) {
            $item->jawabans = $soal_jawabans_indexeds->get($item->id, new Collection())->values();
            return $item;
        });

        $soals_indexeds = $soals->keyBy('id');
        $find = $find->map(function ($item) use ($soals_indexeds) {
            $item->soal = $soals_indexeds->get($item->soal_id);
            return $item;
        });

        $result = [];
        foreach ($find as $item) {
            # Jik tipe soal adalah menjodohkan
            if ($item->soal->tipe_soal == SoalConstant::TIPE_MENJODOHKAN) {
                $jwra = [];
                $jwrb = [];

                if ($item->menjodohkan != null) {
                    $objMenjodohkan = json_decode($item->menjodohkan, true);
                    foreach ($objMenjodohkan as $key => $val) {
                        foreach($item->soal->jawabans as $jwb) {
                            $jwb_arr = json_decode($jwb->text_jawaban, true);
                            if ($val[0] == $jwb_arr['a']['id']) {
                                $jwra[$key] = [
                                    'id' => $jwb_arr['a']['id'],
                                    'text' => $jwb_arr['a']['text']
                                ];
                            }
                            if ($val[1] == $jwb_arr['b']['id']) {
                                $jwrb[$key] = [
                                    'id' => $jwb_arr['b']['id'],
                                    'text' => $jwb_arr['b']['text']
                                ];
                            }
                        }
                    }
                } else {
                    foreach($item->soal->jawabans as $key => $jwb) {
                        $jwb_arr = json_decode($jwb->text_jawaban, true);
                        $jwra[] = [
                            'id' => $jwb_arr['a']['id'],
                            'text' => $jwb_arr['a']['text'],
                        ];
                        $jwrb[] = [
                            'id' => $jwb_arr['b']['id'],
                            'text' => $jwb_arr['b']['text'],
                        ];
                    }

                    $jwra = Arr::shuffle($jwra);
                    $jwrb = Arr::shuffle($jwrb);
                }
            }

            # Jika tipe soal adalah mengurutkan
            if ($item->soal->tipe_soal == SoalConstant::TIPE_MENGURUTKAN) {
                if ($item->mengurutkan == null) {
                    $item->soal->jawabans = Arr::shuffle($item->soal->jawabans->toArray());
                } else {
                    $new_jwbns = [];
                    $objMengurutkan = json_decode($item->mengurutkan, true);
                    foreach ($objMengurutkan as $urut) {
                        foreach($item->soal->jawabans as $key => $jwb) {
                            if ($urut == $jwb->id) {
                                array_push($new_jwbns, $jwb);
                                break;
                            }
                        }
                    }
                    $item->soal->jawabans = $new_jwbns;
                }
            }

            # Jika tipe soal adalah benar salah
            if ($item->soal->tipe_soal == SoalConstant::TIPE_BENAR_SALAH) {

                if ($item->benar_salah == null) {
                    $new_jwb_benar_salah = [];

                    foreach ($item->soal->jawabans as $v) {
                        $new_jwb_benar_salah[$v->id] = 0;
                    }
                    $item->benar_salah = $new_jwb_benar_salah;
                } else {
                    $objBenarSalah = json_decode($item->benar_salah, true);
                    $item->benar_salah = $objBenarSalah;
                }
            }

            # Jika tipe soal adalah setuju tidak
            if ($item->soal->tipe_soal == SoalConstant::TIPE_SETUJU_TIDAK) {

                if ($item->setuju_tidak == null) {
                    $new_jwb_setuju_tidak = [];

                    foreach ($item->soal->jawabans as $v) {
                        $new_jwb_setuju_tidak[$v->id]['val'] = 0;
                        $new_jwb_setuju_tidak[$v->id]['argument'] = "";
                    }
                    $item->setuju_tidak = $new_jwb_setuju_tidak;
                } else {
                    $objSetujuTidak = json_decode($item->setuju_tidak, true);
                    $item->setuju_tidak = $objSetujuTidak;
                }
            }


            # FINALIZE DATA YEEE, YOU ARE EXTRAORDINARY SHELLREAN GREAT JOBS
            if ($item->soal->tipe_soal == SoalConstant::TIPE_MENJODOHKAN) {
                $jawabans = $item->soal->jawabans->map(function($jw, $index) use ($jwra, $jwrb) {
                    return [
                        'a' => $jwra[$index],
                        'b' => $jwrb[$index],
                    ];
                });
            } else {
                # Pada soal listening kita tidak boleh untuk mengacak opsi
                # jadi kita urutkan lagi
                if($item->soal->tipe_soal == SoalConstant::TIPE_LISTENING) {
                    $jawabans = $item->soal->jawabans->sortBy('created_at')->values();
                } else {
                    $jawabans = $item->soal->jawabans;
                }
            }

            $result[] = [
                'id'            => $item->id,
                'banksoal_id'   => $item->banksoal_id,
                'soal_id'       => $item->soal_id,
                'jawab'         => $item->jawab,
                'esay'          => $item->esay,
                'jawab_complex' => json_decode($item->jawab_complex),
                'benar_salah'   => $item->benar_salah,
                'setuju_tidak'  => $item->setuju_tidak,
                'answered'      => $item->answered,
                'soal' => [
                    'audio'         => $item->soal->audio,
                    'banksoal_id'   => $item->soal->banksoal_id,
                    'direction'     => $item->soal->direction,
                    'id'            => $item->soal->id,
                    'jawabans'      => $jawabans,
                    'pertanyaan'    => $item->soal->pertanyaan,
                    'tipe_soal'     => intval($item->soal->tipe_soal),
                    'layout'        => intval($item->soal->layout),
                ],
                'ragu_ragu'     => $item->ragu_ragu,
            ];
        }

        return $result;
    }

    /**
     * Finishing ujian
     *
     * @param string $banksoal_id
     * @param string $jadwal_id
     * @param string $peserta_id
     * @return bool
     * @throws Exception
     * @since 3.0.0 <ristretto>
     */
    public function finishing(string $banksoal_id, string $jadwal_id, string $peserta_id, string $ujian_id)
    {
        # Ambil banksoal
        if (config('exo.enable_cache')) {
            $cacheKeyConsolidate = "banksoal_1838471746_".$banksoal_id;
            if(Cache::has($cacheKeyConsolidate)) {
                $banksoal = Cache::get($cacheKeyConsolidate);
            } else {
                $banksoal = Banksoal::find($banksoal_id);
                if($banksoal) {
                    Cache::put($cacheKeyConsolidate, $banksoal, 60);

                }
            }

        } else {
            $banksoal = Banksoal::find($banksoal_id);
        }

        if (!$banksoal) {
            throw new Exception('banksoal tidak ditemukan');
        }

        try {
            # Tipe soal: pilihan ganda
            $hasil_pg = 0;
            $pg_benar = 0;
            $pg_salah = 0;
            if($banksoal->jumlah_soal > 0) {
                $pg_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_PG);
                $pg_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_PG);

                if ($pg_benar > 0) {
                    $hasil_pg = ($pg_benar/$banksoal->jumlah_soal) * $banksoal->persen['pilihan_ganda'];
                }
            }


            # Tipe soal: pilihan ganda komplex
            $hasil_mpg = 0;
            $mpg_salah = 0;
            $mpg_benar = 0;
            if($banksoal->jumlah_soal_ganda_kompleks > 0) {
                $mpg_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_PG_KOMPLEK);
                $mpg_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_PG_KOMPLEK);

                if($mpg_benar > 0) {
                    $hasil_mpg = ($mpg_benar/$banksoal->jumlah_soal_ganda_kompleks)*$banksoal->persen['pilihan_ganda_komplek'];
                }
            }

            # Tipe soal: listening
            $hasil_listening = 0;
            $listening_benar = 0;
            $listening_salah = 0;
            if($banksoal->jumlah_soal_listening > 0) {
                $listening_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_LISTENING);
                $listening_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_LISTENING);

                if($listening_benar > 0) {
                    $hasil_listening = ($listening_benar/$banksoal->jumlah_soal_listening)*$banksoal->persen['listening'];
                }
            }

            # Tipe soal: isian singkat
            $hasil_isiang_singkat = 0;
            $isian_singkat_benar = 0;
            $isian_singkat_salah = 0;
            if($banksoal->jumlah_isian_singkat > 0) {
                $isian_singkat_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_ISIAN_SINGKAT);
                $isian_singkat_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_ISIAN_SINGKAT);

                if($isian_singkat_benar > 0) {
                    $hasil_isiang_singkat = ($isian_singkat_benar/$banksoal->jumlah_isian_singkat)*$banksoal->persen['isian_singkat'];
                }
            }

            # Tipe soal: menjodohkan
            $hasil_menjodohkan = 0;
            $jumlah_menjodohkan_benar = 0;
            $jumlah_menjodohkan_salah = 0;
            if($banksoal->jumlah_menjodohkan > 0) {
                $jumlah_menjodohkan_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_MENJODOHKAN);
                $jumlah_menjodohkan_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_MENJODOHKAN);

                if($jumlah_menjodohkan_benar > 0) {
                    $hasil_menjodohkan = ($jumlah_menjodohkan_benar/$banksoal->jumlah_menjodohkan)*$banksoal->persen['menjodohkan'];
                }
            }

            # Tipe soal: mengurutkan
            $hasil_mengurutkan = 0;
            $jumlah_mengurutkan_benar = 0;
            $jumlah_mengurutkan_salah = 0;
            if($banksoal->jumlah_mengurutkan > 0) {
                $jumlah_mengurutkan_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_MENGURUTKAN);
                $jumlah_mengurutkan_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_MENGURUTKAN);

                if($jumlah_mengurutkan_benar > 0) {
                    $hasil_mengurutkan = ($jumlah_mengurutkan_benar/$banksoal->jumlah_mengurutkan)*$banksoal->persen['mengurutkan'];
                }
            }

            # Tipe soal: benar/salah
            $hasil_benar_salah = 0;
            $jumlah_benar_salah_benar = 0;
            $jumlah_benar_salah_salah = 0;
            if($banksoal->jumlah_benar_salah > 0) {
                $jumlah_benar_salah_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_BENAR_SALAH);
                $jumlah_benar_salah_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, SoalConstant::TIPE_BENAR_SALAH);

                if($jumlah_benar_salah_benar > 0) {
                    $hasil_benar_salah = ($jumlah_benar_salah_benar/$banksoal->jumlah_benar_salah)*$banksoal->persen['benar_salah'];
                }
            }

            # Resulting Score
            $null = JawabanPeserta::where([
                'jawab'         => 0,
                'jadwal_id'     => $jadwal_id,
                'peserta_id'    => $peserta_id,
            ])->where('answered', false)->count();

            $hasil = $hasil_pg+$hasil_listening+$hasil_mpg+$hasil_isiang_singkat+$hasil_menjodohkan+$hasil_mengurutkan+$hasil_benar_salah;

            DB::table('hasil_ujians')->insert([
                'id'                            => Str::uuid()->toString(),
                'ujian_id'                      => $ujian_id,
                'banksoal_id'                   => $banksoal_id,
                'peserta_id'                    => $peserta_id,
                'jadwal_id'                     => $jadwal_id,
                'jumlah_salah'                  => $pg_salah,
                'jumlah_benar'                  => $pg_benar,
                'jumlah_benar_complek'          => $mpg_benar,
                'jumlah_salah_complek'          => $mpg_salah,
                'jumlah_benar_listening'        => $listening_benar,
                'jumlah_salah_listening'        => $listening_salah,
                'jumlah_benar_isian_singkat'    => $isian_singkat_benar,
                'jumlah_salah_isian_singkat'    => $isian_singkat_salah,
                'jumlah_benar_menjodohkan'      => $jumlah_menjodohkan_benar,
                'jumlah_salah_menjodohkan'      => $jumlah_menjodohkan_salah,
                'jumlah_benar_mengurutkan'      => $jumlah_mengurutkan_benar,
                'jumlah_salah_mengurutkan'      => $jumlah_mengurutkan_salah,
                'jumlah_benar_benar_salah'      => $jumlah_benar_salah_benar,
                'jumlah_salah_benar_salah'      => $jumlah_benar_salah_salah,
                'tidak_diisi'                   => $null,
                'hasil'                         => $hasil,
                'point_esay'                    => 0,
                'point_setuju_tidak'            => 0,
                'created_at'                    => now(),
                'updated_at'                    => now()
            ]);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return true;
    }

    /**
     * Count wrong answer
     *
     * @param string $jadwal_id
     * @param string $peserta_id
     * @param string $type
     * @return int
     * @since 3.0.0 <ristretto>
     */
    private function _countWrongAnswer(string $jadwal_id, string $peserta_id, $type)
    {
        $salah = JawabanPeserta::where([
            'iscorrect'     => 0,
            'jadwal_id'     => $jadwal_id,
            'peserta_id'    => $peserta_id,
        ])
        ->whereHas('soal', function($query) use ($type) {
            $query->where('tipe_soal',$type);
        })
        ->count();

        return $salah;
    }

    /**
     * Count correct answer
     *
     * @param string $jadwal_id
     * @param string $peserta_id
     * @param string $type
     * @return int
     * @since 3.0.0 <ristretto>
     */
    private function _countCorrectAnswer(string $jadwal_id, string $peserta_id, $type)
    {
        $benar = JawabanPeserta::where([
            'iscorrect'     => 1,
            'jadwal_id'     => $jadwal_id,
            'peserta_id'    => $peserta_id,
        ])
        ->whereHas('soal', function($query) use ($type) {
            $query->where('tipe_soal',$type);
        })
        ->count();

        return $benar;
    }

    /**
     * Decrease ujian reminning
     *
     * @param object $siswa_ujian
     * @return void
     * @throws Exception
     * @since 3.0.0 <ristretto>
     */
    public function updateReminingTime(object $siswa_ujian)
    {
        $deUjian = $this->jadwalService->findJadwal($siswa_ujian->jadwal_id);

        # hitung perbedaan waktu
        # shadow dan waktu sekarang
        $start = Carbon::createFromFormat('H:i:s', $siswa_ujian->mulai_ujian_shadow);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        try {
            DB::table('siswa_ujians')
                ->where('id', $siswa_ujian->id)
                ->update([
                    'sisa_waktu'    => intval($deUjian->lama)-intval($diff_in_minutes)
                ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
