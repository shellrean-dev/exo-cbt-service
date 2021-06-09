<?php declare(strict_types=1);

namespace ShellreanDev\Services\Jadwal;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use ShellreanDev\Utils\Error;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;
use stdClass;

/**
 * Jadwal Service
 * 
 * @since 3.0.0 <ristretto>
 * @author shellrean <wandinak17@gmail.com>
 */
final class JadwalService extends AbstractService
{
    /**
     * Inject dependency
     * 
     * @since 3.0.0 <ristretto>
     * @param ShellreanDev\Cache\CacheHandler $cache
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get ujian active today
     * 
     * @return Illuminate\Support\Collection
     * @since 3.0.0 <ristretto>
     */
    public function activeToday(): ?Collection
    {
        $key = md5(sprintf('jadwal:data:active:today'));
        if ($this->cache->isCached($key)) {
            $jadwals = $this->cache->getItem($key);
        } else {
            $jadwals = DB::table('jadwals')->where([
                'status_ujian'  => 1,
                'tanggal'       => now()->format('Y-m-d')
            ])
            ->select(
                'id',
                'alias',
                'banksoal_id',
                'lama',
                'mulai',
                'tanggal',
                'setting',
                'group_ids'
            )
            ->get();

            $this->cache->cache($key, $jadwals);
        }

        return $jadwals;
    }

    /**
     * Get ujian has finished by student
     * 
     * @param string $student_id
     * @return Illuminate\Support\Collection
     * @since 3.0.0 <ristretto>
     */
    public function hasCompletedBy(string $student_id): ?Collection
    {
        $key = md5(sprintf('jadwal:data:peserta:%s:ujian:complete', $student_id));
        if ($this->cache->isCached($key)) {
            $hascomplete = $this->cache->getItem($key);
        } else {
            $hascomplete = DB::table('siswa_ujians')->where([
                'peserta_id'        => $student_id,
                'status_ujian'      => 1
            ])
            ->select('jadwal_id')
            ->get()
            ->pluck('jadwal_id');

            $this->cache->cache($key, $hascomplete);
        }

        return $hascomplete;
    }
}