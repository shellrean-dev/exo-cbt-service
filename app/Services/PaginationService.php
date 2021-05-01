<?php declare(strict_types=1);

namespace ShellreanDev\Services;

use ShellreanDev\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use ShellreanDev\Cache\CacheHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ShellreanDev\Utils\Error;

/**
 * Pagination service
 * @author shellrean <wandinak17@gmail.com>
 */
final class PaginationService
{
    /**
     * Cache handler
     * @var CacheHandler $cache
     */
    private $cache;

    /**
     * @param CacheHandler $cache
     * @return void
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Create pagination data
     * @param RepositoryInterface $repository
     * @param array $conditions
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function build(RepositoryInterface $repository, array $conditions, int $perPage = 10): ?LengthAwarePaginator
    {
        try {
            $data = DB::table($repository->getTable());
            if ($conditions) {
                $data = $data->where($conditions);
            }
            $data = $data->paginate($perPage);
        } catch (\Exception $e) {
            if (config('exo.log')) {
                Log::emergency([Error::get($e)]);
            }
            return null;
        }

        return $data;
    }
}