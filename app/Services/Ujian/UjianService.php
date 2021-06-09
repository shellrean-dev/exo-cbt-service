<?php declare(strict_types=1);

namespace ShellreanDev\Services\Ujian;

use stdClass;
use Exception;
use Carbon\Carbon;

use App\Banksoal;
use App\Peserta;
use App\JawabanPeserta;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use ShellreanDev\Utils\Error;
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
    protected $jadwalService;
    
    /**
     * Inject dependency
     * 
     * @param ShellreanDev\Cache\CacheHandler $cache
     * @param ShellreanDev\Services\Jadwal\JadwalService $jadwalService
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
     * @since 3.0.0 <ristretto>
     */
    public function onWorkingToday(string $student_id)
    {
        // ambil ujian yang aktif hari ini
        $jadwals = $this->jadwalService->activeToday();

        $jadwal_ids = $jadwals->pluck('id')->toArray();

        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $key = md5(sprintf('ujian:onwork:today:student:%s:jadwal:%s', $student_id, implode(',', $jadwal_ids)));
        if ($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            $data = DB::table('siswa_ujians')
                ->where('peserta_id', $student_id)
                ->whereIn('status_ujian', [0,3])
                ->whereIn('jadwal_id', $jadwal_ids)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->select('jadwal_id', 'status_ujian')
                ->first();

            $this->cache->cache($key, $data);
        }

        return $data;
    }

    /**
     * Get ujian on standby today
     * 
     * @param string $student_id
     * @since 3.0.0 <ristretto>
     */
    public function onStandbyToday(string $student_id)
    {
        // ambil ujian yang aktif hari ini
        $jadwals = $this->jadwalService->activeToday();

        $jadwal_ids = $jadwals->pluck('id')->toArray();

        // ambil data siswa ujian
        // yang sudah dijalankan pada hari ini
        // tetapi belum dimulai
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $key = md5(sprintf('ujian:onstandby:today:student:%s:jadwal:%s', $student_id, implode(',', $jadwal_ids)));
        if ($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            $data = DB::table('siswa_ujians')
                ->where('peserta_id', $student_id)
                ->where('status_ujian', 0)
                ->whereIn('jadwal_id', $jadwal_ids)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->first();

            $this->cache->cache($key, $data);
        }

        return $data;
    }

    /**
     * Get ujian on progress today
     * 
     * @param string $student_id
     * @since 3.0.0 <ristretto>
     */
    public function onProgressToday(string $student_id)
    {
        // ambil ujian yang aktif hari ini
        $jadwals = $this->jadwalService->activeToday();

        $jadwal_ids = $jadwals->pluck('id')->toArray();

        // ambil data siswa ujian
        // yang sedang dikerjakan pada hari ini
        // yang mana jadwal tersebut sedang aktif dan tanggal pengerjaannya hari ini
        $key = md5(sprintf('ujian:onprogress:today:student:%s:jadwal:%s', $student_id, implode(',', $jadwal_ids)));
        if ($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            $data = DB::table('siswa_ujians')
                ->where('peserta_id', $student_id)
                ->where('status_ujian', 3)
                ->whereIn('jadwal_id', $jadwal_ids)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->first();

            $this->cache->cache($key, $data);
        }

        return $data;
    }

    /**
     * Is peserta's banksoal
     * 
     * @param App\Banksoal $banksoal
     * @param App\Peserta $peserta
     * @return string
     * @since 3.0.0 <ristretto>
     */
    public function checkPesertaBanksoal($banksoal, Peserta $peserta)
    {
        $banksoal_id = '';
        try {
            if ($banksoal->agama_id != 0) {
                if ($banksoal->agama_id == $peserta->agama_id) {
                    $banksoal_id = $banksoal->id;
                }
            } else {
                $jurusans = ($banksoal->jurusan_id == '0' || $banksoal->jurusan_id == '') 
                    ? 0 
                    : json_decode($banksoal->jurusan_id, true);
                if(is_array($jurusans)) {
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
        } catch (\Exception $e) {
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
     * @return object
     * @since 3.0.0 <ristretto>
     */
    public function pesertaAnswers(string $jadwal_id, string $peserta_id, string $acak_opsi)
    {
        // ambil jawaban peserta
        $key = md5(sprintf('jawaban_pesertas:jadwal:%s:peserta:%s:acak:%s', $jadwal_id, $peserta_id, $acak_opsi));
        if ($this->cache->isCached($key)) {
            $find = $this->cache->getItem($key);
        } else {
            $find = JawabanPeserta::with([
                'soal' => function ($q) {
                    $q->select('id','banksoal_id','pertanyaan','tipe_soal','audio','direction','layout');
                },
                'soal.jawabans' => function ($q) use ($acak_opsi) {
                    $q->select('id','soal_id','text_jawaban');
                    if($acak_opsi == "1") {
                        $q->inRandomOrder();
                    }
                }
            ])
            ->where([
                'peserta_id'    => $peserta_id,
                'jadwal_id'     => $jadwal_id,
            ])
            ->select('id','banksoal_id','soal_id','jawab','esay','jawab_complex','ragu_ragu')
            ->orderBy('created_at')
            ->get()
            ->makeHidden('similiar');

            if ($find->count() > 0) {
                $this->cache->cache($key, $find);
            }
        }

        $data = $find->map(function($item) {
            if ($item->soal->tipe_soal == 5) {
                $jwra = [];
                $jwrb = [];
                foreach($item->soal->jawabans as $key => $jwb) {
                    $jwb_arr = json_decode($jwb->text_jawaban, true);
                    array_push($jwra, [
                        'id' => $jwb_arr['a']['id'],
                        'text' => $jwb_arr['a']['text'],
                    ]);
                    array_push($jwrb, [
                        'id' => $jwb_arr['b']['id'],
                        'text' => $jwb_arr['b']['text'],
                    ]);
                }

                $jwra = Arr::shuffle($jwra);
                $jwrb = Arr::shuffle($jwrb);
            }


            $jawabans = [];
            if (in_array($item->soal->tipe_soal, [1,2,3,4,5])) {
                $jawabans = in_array($item->soal->tipe_soal, [1,2,3,4])
                    ? $item->soal->jawabans
                    : $item->soal->jawabans->map(function($jw, $index) use ($jwra, $jwrb){
                    return [
                        'a' => $jwra[$index],
                        'b' => $jwrb[$index],
                    ];
                });
            }

            return [
                'id'    => $item->id,
                'banksoal_id' => $item->banksoal_id,
                'soal_id' => $item->soal_id,
                'jawab' => $item->jawab,
                'esay' => $item->esay,
                'jawab_complex' => $item->jawab_complex,
                'soal' => [
                    'audio' => $item->soal->audio,
                    'banksoal_id' => $item->soal->banksoal_id,
                    'direction' => $item->soal->direction,
                    'id' => $item->soal->id,
                    'jawabans' => $jawabans,
                    'pertanyaan' => $item->soal->pertanyaan,
                    'tipe_soal' => $item->soal->tipe_soal,
                    'layout'    => $item->soal->layout,
                ],
                'ragu_ragu' => $item->ragu_ragu,
            ];
        });
        return $data;
    }

    /**
     * Finishing ujian
     * 
     * @param string $banksoal_id
     * @param string $jadwal_id
     * @param string $peserta_id
     * @return object
     * @since 3.0.0 <ristretto>
     */
    public function finishing(string $banksoal_id, string $jadwal_id, string $peserta_id)
    {
        // Ambil banksoal
        $key = md5(sprintf('banksoal:data:%s:single', $banksoal_id));
        if ($this->cache->isCached($key)) {
            $banksoal = $this->cache->getItem($key);
        } else {
            $banksoal = Banksoal::find($banksoal_id);

            $this->cache->cache($key, $banksoal);
        }

        if (!$banksoal) {
            throw new Exception('banksoal tidak ditemukan');
        }

        try {
            // Tipe soal: pilihan ganda
            $hasil_pg = 0;
            $pg_benar = 0;
            $pg_salah = 0;
            if($banksoal->jumlah_soal > 0) {
                $pg_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, '1');
                $pg_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, '1');

                if ($pg_benar > 0) {
                    $hasil_pg = ($pg_benar/$banksoal->jumlah_soal) * $banksoal->persen['pilihan_ganda'];
                }
            }


            // Tipe soal: pilihan ganda komplex
            $hasil_mpg = 0;
            $mpg_salah = 0;
            $mpg_benar = 0;
            if($banksoal->jumlah_soal_ganda_kompleks > 0) {
                $mpg_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, '4');
                $mpg_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, '4');

                if($mpg_benar > 0) {
                    $hasil_mpg = ($mpg_benar/$banksoal->jumlah_soal_ganda_kompleks)*$banksoal->persen['pilihan_ganda_komplek'];
                }
            }

            // Tipe soal: listening
            $hasil_listening = 0;
            $listening_benar = 0;
            $listening_salah = 0;
            if($banksoal->jumlah_soal_listening > 0) {
                $listening_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, '3');
                $listening_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, '3');

                if($listening_benar > 0) {
                    $hasil_listening = ($listening_benar/$banksoal->jumlah_soal_listening)*$banksoal->persen['listening'];
                }
            }

            // Tipe soal: isian singkat
            $hasil_isiang_singkat = 0;
            $isian_singkat_benar = 0;
            $isian_singkat_salah = 0;
            if($banksoal->jumlah_isian_singkat > 0) {
                $isian_singkat_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, '6');
                $isian_singkat_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, '6');

                if($isian_singkat_benar > 0) {
                    $hasil_isiang_singkat = ($isian_singkat_benar/$banksoal->jumlah_isian_singkat)*$banksoal->persen['isian_singkat'];
                }
            }

            // Tipe soal: menjodohkan
            $hasil_menjodohkan = 0;
            $jumlah_menjodohkan_benar = 0;
            $jumlah_menjodohkan_salah = 0;
            if($banksoal->jumlah_menjodohkan > 0) {
                $jumlah_menjodohkan_benar = $this->_countCorrectAnswer($jadwal_id, $peserta_id, '5');
                $jumlah_menjodohkan_salah = $this->_countWrongAnswer($jadwal_id, $peserta_id, '5');

                if($jumlah_menjodohkan_benar > 0) {
                    $hasil_isiang_singkat = ($jumlah_menjodohkan_benar/$banksoal->jumlah_menjodohkan)*$banksoal->persen['menjodohkan'];
                }
            }

            // Resulting Score
            $null = JawabanPeserta::where([
                'jawab'         => 0,
                'jadwal_id'     => $jadwal_id,
                'peserta_id'    => $peserta_id,
            ])
            ->whereHas('soal', function($query) {
                $query->whereIn('tipe_soal',['1','3','4','5','6']);
            })
            ->count();

            $hasil = $hasil_pg+$hasil_listening+$hasil_mpg+$hasil_isiang_singkat+$hasil_menjodohkan;

            DB::table('hasil_ujians')->insert([
                'id'                            => Str::uuid()->toString(),
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
                'tidak_diisi'                   => $null,
                'hasil'                         => $hasil,
                'point_esay'                    => 0,
                'created_at'                    => now(),
                'updated_at'                    => now()
            ]);

            // remove completed jadwal cache
            $key = md5(sprintf('jadwal:data:peserta:%s:ujian:complete', $peserta_id));
            $this->cache->cache($key, '', 0);
            
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
    private function _countWrongAnswer(string $jadwal_id, string $peserta_id, string $type) 
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
    private function _countCorrectAnswer(string $jadwal_id, string $peserta_id, string $type) 
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
     * @since 3.0.0 <ristretto>
     */
    public function updateReminingTime(object $siswa_ujian)
    {
        $key = md5(sprintf('jadwals:data:%s:single', $siswa_ujian->jadwal_id));
        if ($this->cache->isCached($key)) {
            $deUjian = $this->cache->getItem($key);
        } else {
            $deUjian = DB::table('jadwals')
                ->where('id', $siswa_ujian->jadwal_id)
                ->first();
            
            $this->cache->cache($key, $deUjian);
        }

        // hitung perbedaan waktu
        // shadow dan waktu sekarang
        $start = Carbon::createFromFormat('H:i:s', $siswa_ujian->mulai_ujian_shadow);
        $now = Carbon::createFromFormat('H:i:s', Carbon::now()->format('H:i:s'));
        $diff_in_minutes = $start->diffInSeconds($now);

        try {
            DB::table('siswa_ujians')
                ->where('id', $siswa_ujian->id)
                ->update([
                    'sisa_waktu'    => $deUjian->lama-$diff_in_minutes
                ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}