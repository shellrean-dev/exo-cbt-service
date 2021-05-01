<?php

declare(strict_types=1);

namespace ShellreanDev\Cache;

use Illuminate\Support\Facades\Cache;

/**
 * @author shellrean
 */
final class CacheHandler implements CacheHandlerInterface
{
    /**
     * Set cache data
     * @param string $key
     * @param $data
     * @param int $seconds
     */
    public function cache(string $key, $data, int $seconds = 60): void
    {
        Cache::put(md5($key), $data, $seconds);
    }

    /**
     * Get cache data
     * @param string $key
     */
    public function getItem(string $key)
    {
        $value = Cache::get(md5($key));
        return $value;
    }

    /**
     * Check for item existence
     * @param string $key
     */
    public function isCached(string $key): bool
    {
        return Cache::has(md5($key));
    }
}