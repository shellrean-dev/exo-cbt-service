<?php declare(strict_types=1);

namespace ShellreanDev\Services\User;

use Illuminate\Support\Facades\Log;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;
use ShellreanDev\Repositories\User\UserRepository;

/**
 * UserService service
 * @author shellrean <wandinak17@gmail.com>
 */
final class UserService extends AbstractService
{
    /**
     * Dependency injection
     * @param CacheHandler $cache
     * @param UserRepository $repository
     */
    public function __construct(CacheHandler $cache, UserRepository $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }
}