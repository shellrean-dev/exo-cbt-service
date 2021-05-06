<?php

declare(strict_types=1);

namespace ShellreanDev\Services;

use ShellreanDev\Repositories\RepositoryInterface;
use ShellreanDev\Cache\CacheHandlerInterface;
use ShellreanDev\Cache\CacheHandler;
use Illuminate\Support\Facades\Log;
use stdClass;

/**
 * Abstract service
 * @author shellrean
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * Repository of service
     * @var RepositoryInterface
     */
    protected RepositoryInterface $repository;

    /**
     * Cache handler
     * @var CacheHandlerInterface
     */
    protected CacheHandlerInterface $cache;

    /**
     * Fetch data from repository
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function fetch(int $limit = 10, int $offset = 0)
    {
        $key = sprintf('%s:%s:%d:%d',get_class($this->repository), 'fetch',$limit,$offset);
        if ($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            $data = $this->repository->fetch($limit, $offset)->getEntities();
            if ($data) {
                $this->cache->cache($key, $data);
            }
        }
        return $data;
    }

    /**
     * Store data to repository
     * @param stdClass $input
     * @return self
     */
    public function store(stdClass $input): ?stdClass
    {
        $store = $this->repository->store($input);
        if ($store->getErrors()) {
            if (config('exo.log')) {
                Log::emergency($store->getErrors());
            }
            return null;
        }
        $data = $store->getEntity();
        $key = sprintf('%s:%s:%s',get_class($this->repository), 'data',$data->{$store->getPrimaryKey()});
        $this->cache->cache($key, $data);

        return $data;
    }

    /**
     * Update data in repository
     * @param stdClass $input
     * @return stdClass
     */
    public function update(stdClass $input): ?stdClass
    {
        $update = $this->repository->update($input);
        if ($update->getErrors()) {
            if (config('exo.log')) {
                Log::emergency($update->getErrors());
            }
            return null;
        }
        $data = $update->getEntity();
        return $data;
    }

    /**
     * Get data from repository
     * @param $id
     * @return stdClass
     */
    public function find($id): ?stdClass
    {
        $find = $this->repository->findOne($id);
        if ($find->getErrors()) {
            if (config('exo.log')) {
                Log::emergency($find->getErrors());
            }
            return null;
        }
        $data = $find->getEntity();
        return $data;
    }

    /**
     * Destroy data in repository
     * @param $id
     * @return bool
     */
    public function destroy($id): bool
    {
        $destroy = $this->repository->destroy($id);
        if ($destroy->getErrors()) {
            return false;
        }
        return true;
    }
}