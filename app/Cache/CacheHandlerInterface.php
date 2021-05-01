<?php

declare(strict_types=1);

namespace ShellreanDev\Cache;

/**
 * Cache handler interface
 * @author shellrean
 */
interface CacheHandlerInterface
{
    public function cache(string $key, $data, int $seconds): void;

    public function isCached(string $key): bool;

    public function getItem(string $key);
}