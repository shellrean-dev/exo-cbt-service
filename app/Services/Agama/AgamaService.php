<?php

declare(strict_types=1);

namespace ShellreanDev\Services\Agama;

use ShellreanDev\Repositories\Agama\AgamaRepository;
use ShellreanDev\Services\AbstractService;
use ShellreanDev\Cache\CacheHandler;
use Illuminate\Support\Facades\Log;

/**
 * Agama service
 * @author shellrean <wandinak17@gmail.com>
 */
final class AgamaService extends AbstractService
{
    /**
     * Dependenc injection
     */
    public function __construct(CacheHandler $cache, AgamaRepository $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }

    /**
     * Get all data source
     * @return iterable
     */
    public function fetchAll(): ?iterable
    {
        // First we want to check is there existence cache
        $key = sprintf('%s:%s:%s', get_class($this->repository), 'agamas', 'all-data');
        if ($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            // retreive data from repository
            $fetch = $this->repository->fetchAll();
            // check if there any error message
            if ($fetch->getErrors()) {
                if (config('exo.allow_loggin')) {
                    Log::emergency($fetch->getErrors());
                }
                return null;
            }

            $data = $fetch->getEntities();

            // cache data result
            $this->cache->cache($key, $data);
        }
        return $data;
    }
}