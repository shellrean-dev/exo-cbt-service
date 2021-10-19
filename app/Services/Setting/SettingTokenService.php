<?php

namespace App\Services\Setting;

use App\Models\CacheConstant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use ShellreanDev\Cache\CacheHandler;

/**
 * Setting token service
 *
 * @author shellrean <wandinak17@gmail.com>
 * @since 3.0.1 <expresso>
 */
class SettingTokenService implements SettingServiceInterface
{
    /**
     * @var string
     */
    private const FIELD_SETTING = "token";

    /**
     * @var CacheHandler
     */
    private CacheHandler $cache;

    /**
     * @param CacheHandler $cache
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return Model|Builder|mixed|object|null
     */
    public function getSetting()
    {
        $query = DB::table('settings')->where('name', self::FIELD_SETTING);
        if (config('exo.enable_cache')) {
            $is_cached = $this->cache->isCached(CacheConstant::KEY_SETTING, self::FIELD_SETTING);
            if ($is_cached) {
                $setting = $this->cache->getItem(CacheConstant::KEY_SETTING, self::FIELD_SETTING);
            } else {
                $setting = $query->first();
                if ($setting) {
                    $this->cache->cache(CacheConstant::KEY_SETTING, self::FIELD_SETTING, $setting, 60);
                }
            }
        } else {
            $setting = $query->first();
        }
        return $setting;
    }

    /**
     * @param string $value
     * @return bool
     */
    public function setSetting(string $value): bool
    {
        DB::table('settings')->where('name', self::FIELD_SETTING)
            ->update(['value' => $value]);
        if (config('exo.enable_cache')) {
            $this->cache->cache(CacheConstant::KEY_SETTING, self::FIELD_SETTING, $setting, 0);
        }
        return true;
    }
}
