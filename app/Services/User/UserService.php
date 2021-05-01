<?php declare(strict_types=1);

namespace ShellreanDev\Services\User;

use Illuminate\Support\Facades\Log;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;

/**
 * UserService service
 * @author shellrean <wandinak17@gmail.com>
 */
final class UserService extends AbstractService
{
    /**
     * Dependency injection
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }
}