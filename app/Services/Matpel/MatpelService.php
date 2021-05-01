<?php declare(strict_types=1);

namespace ShellreanDev\Services\Matpel;

use App\Imports\MatpelImport;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Maatwebsite\Excel\Facades\Excel;

use ShellreanDev\Utils\Error;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;
use ShellreanDev\Repositories\Matpel\MatpelRepository;

/**
 * MatpelService service
 * @author shellrean <wandinak17@gmail.com>
 */
final class MatpelService extends AbstractService
{
    /**
     * Dependency injection
     */
    public function __construct(CacheHandler $cache, MatpelRepository $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
    }

    /**
     * Get all data source
     * @return interable
     */
    public function fetchAll(): ?iterable
    {
        $key = sprintf('%s:%s:%s', get_class($this->repository), 'matpel', 'fetch-all');
        if($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
        } else {
            $fetch = $this->repository->fetchAll();
            if($fetch->getErrors()) {
                if(config('exo.log')) {
                    Log::emergency($fetch->getErrors());
                }
                return null;
            }

            $data = $fetch->getEntities();
            $data = $data->map(function($item) {
                $item->jurusan_id = json_decode($item->jurusan_id);
                $item->correctors = json_decode($item->correctors);
                return $item;
            });
            $this->cache->cache($key, $data);
        }

        return $data;
    }

    /**
     * Multiple data source
     * @param array $ids
     * @return bool
     */
    public function deletes(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }

        DB::beginTransaction();
        $deletes = $this->repository->deletes($ids);
        if ($deletes->getErrors()) {
            DB::rollBack();
            if (config('exo.log')) {
                Log::emergency($deletes->getErrors());
            }
            return false;
        }
        $key = sprintf('%s:%s:%s', get_class($this->repository), 'matpel', 'fetch-all');
        if($this->cache->isCached($key)) {
            $data = $this->cache->getItem($key);
            $data = $data->filter(function ($v, $k) use ($ids) {
                return !in_array($v->id, $ids);
            });

            $this->cache->cache($key, $data);
        }
        DB::commit();
        return true;
    }

    /**
     * Import data source
     * @param UploadedFile $file
     * @return bool
     */
    public function import(UploadedFile $file): bool
    {
        try {
            Excel::import(new MatpelImport, $file);
        } catch (\Exception $e) {
            if(config('exo.log')) {
                Log::error($e->getMessage(), Error::get($e));
            }
            return false;
        }
        return true;
    }
}