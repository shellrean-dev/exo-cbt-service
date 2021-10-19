<?php

namespace App\Services\Ujian;

use App\Models\CacheConstant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use ShellreanDev\Cache\CacheHandler;

/**
 * Jawaban Peserta Service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.1 <ristretto>
 */
class JawabanPesertaService
{
    /**
     * @var CacheHandler
     * @since 3.0.1 <ristretto>
     */
    private CacheHandler $cache;

    /**
     * @param CacheHandler $cache
     * @since 3.0.1 <ristretto>
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }

    /**
     * get jawaban peserta by id
     *
     * @param $jawaban_id
     * @return Model|Builder|mixed|object|null
     * @since 3.0.1 <ristretto>
     */
    public function getJawaban($jawaban_id)
    {
        $query = DB::table('jawaban_pesertas')
            ->where('id', $jawaban_id);
        if(config('exo.enable_cache')) {
            $is_cached = $this->cache->isCached(CacheConstant::KEY_JAWABAN_PESERTA, $jawaban_id);
            if ($is_cached) {
                $jawaban_peserta = $this->cache->getItem(CacheConstant::KEY_JAWABAN_PESERTA, $jawaban_id);
            } else {
                $jawaban_peserta = $query->first();
                if ($jawaban_peserta) {
                    $this->cache->cache(CacheConstant::KEY_JAWABAN_PESERTA, $jawaban_id, $jawaban_peserta);
                }
            }
        } else {
            $jawaban_peserta = $query->first();
        }
        return $jawaban_peserta;
    }
}
