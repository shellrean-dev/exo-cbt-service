<?php declare(strict_types=1);

namespace ShellreanDev\Services\Jadwal;

use App\Models\CacheConstant;
use App\Models\UjianConstant;
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
     * @cacheable
     *
     * @return object
     * @since 3.0.0 <ristretto>
     */
    public function activeToday()
    {
        $query = DB::table('jadwals')
            ->where([
                'status_ujian'  => 1,
                'tanggal'       => now()->format('Y-m-d')
            ])
            ->select([
                'id',
                'alias',
                'banksoal_id',
                'lama',
                'mulai',
                'tanggal',
                'setting',
                'group_ids',
                'view_result'
            ]);

        if (config('exo.enable_cache')) {
            $is_cached = $this->cache->isCached(CacheConstant::KEY_JADWAL_ACTIVE_TODAY, __METHOD__);
            if ($is_cached) {
                $jadwals = $this->cache->getItem(CacheConstant::KEY_JADWAL_ACTIVE_TODAY, __METHOD__);
            } else {
                $jadwals = $query->get();
                if ($jadwals) {
                    $this->cache->cache(CacheConstant::KEY_JADWAL_ACTIVE_TODAY, __METHOD__, $jadwals);
                }
            }
        } else {
            $jadwals = $query->get();
        }
        return $jadwals;
    }

    /**
     * Get ujian has finished by student
     *
     * @param string $student_id
     * @return object
     * @since 3.0.0 <ristretto>
     */
    public function hasCompletedBy(string $student_id)
    {
        $hascomplete = DB::table('siswa_ujians')->where([
            'peserta_id'        => $student_id,
            'status_ujian'      => UjianConstant::STATUS_FINISHED
        ])->select(['jadwal_id'])->get()->pluck('jadwal_id');

        return $hascomplete;
    }
}
