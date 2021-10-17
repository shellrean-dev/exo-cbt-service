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
     * @param string $name
     * @param string $key
     * @param $data
     * @param int $seconds
     */
    public function cache(string $name, string $key, $data, int $seconds = 5): void
    {
        Cache::put($name.$key, $data, $seconds);
    }

    /**
     * Get cache data
     * @param string $name
     * @param string $key
     * @return mixed
     */
    public function getItem(string $name, string $key)
    {
        return Cache::get($name.$key);
    }

    /**
     * Check for item existence
     * @param string $name
     * @param string $key
     * @return bool
     */
    public function isCached(string $name, string $key): bool
    {
        return Cache::has($name.$key);
    }
}
