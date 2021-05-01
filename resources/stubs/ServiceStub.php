<?php declare(strict_types=1);

namespace DummyNamespace;

use ShellreanDev\Services\AbstractService;
use ShellreanDev\Cache\CacheHandler;
use Illuminate\Support\Facades\Log;

/**
 * DummyClass service
 * @author shellrean <wandinak17@gmail.com>
 */
final class DummyClass extends AbstractService
{
    /**
     * Dependency injection
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }
}