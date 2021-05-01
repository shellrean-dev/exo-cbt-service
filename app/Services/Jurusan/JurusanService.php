<?php declare(strict_types=1);

namespace ShellreanDev\Services\Jurusan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;
use ShellreanDev\Services\PaginationService;
use ShellreanDev\Repositories\Jurusan\JurusanRepository;

/**
 * JurusanService service
 * @author shellrean <wandinak17@gmail.com>
 */
final class JurusanService extends AbstractService
{
    private $pagination;

    /**
     * Dependency injection
     * @param CacheHandler $cache
     * @param JurusanRepository $repository
     */
    public function __construct(CacheHandler $cache, JurusanRepository $repository, PaginationService $pagination)
    {
        $this->cache = $cache;
        $this->repository = $repository;
        $this->pagination = $pagination;
    }

    /**
     * Fetch pagination data
     * @return iterable
     */
    public function paginate(array $condition, int $perPage = 10)
    {
        $paginate = $this->pagination->build($this->repository, $condition, $perPage);
        if (!$paginate) {
            return null;
        }
        return $paginate;
    }

    /**
     * Get all data source
     * @return iterable
     */
    public function fetchAll(): ?iterable
    {
        // First we want to check is there existence cache
        $key = sprintf('%s:%s:%s', get_class($this->repository), 'jurusans', 'all-data');
        if ($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            // retreive data from repository
            $fetch = $this->repository->fetchAll();
            // check if there any error message
            if ($fetch->getErrors()) {
                if (config('exo.log')) {
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

    /**
     * Delete multiple data source
     * @return bool
     */
    public function deletes(array $ids): bool
    {
        // first we create a transaction
        DB::beginTransaction();
        // we will delete data with given ids
        $delete = $this->repository->deletes($ids);

        // check if there any error message
        if ($delete->getErrors()) {
            // rollback the change
            DB::rollBack();
            if (config('exo.log')) {
                Log::emergency($delete->getErrors());
            }
            return false;
        }
        DB::commit();
        return true;
    }
}