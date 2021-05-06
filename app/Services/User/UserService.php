<?php declare(strict_types=1);

namespace ShellreanDev\Services\User;

use App\Imports\UserImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

use ShellreanDev\Utils\Error;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;
use ShellreanDev\Repositories\User\UserRepository;
use ShellreanDev\Services\PaginationService;

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
    public function __construct(CacheHandler $cache, UserRepository $repository, PaginationService $pagination)
    {
        $this->cache = $cache;
        $this->repository = $repository;
        $this->pagination = $pagination;
    }

    /**
     * Create pagination data
     * @param array $conditions
     * @param int $limit
     */
    public function paginate(array $conditions, int $limit)
    {
        $paginate = $this->pagination->build($this->repository, $conditions, $limit);
        if (!$paginate) {
            return null;
        }
        $paginate = (object) $paginate->toArray();
        $paginate->data = array_map(function($item) {
            unset($item->password);
            unset($item->email_verified_at);
            unset($item->created_at);
            unset($item->updated_at);
            return $item;
        }, $paginate->data);

        return $paginate;
    }

    /**
     * Get single data source
     * @param string $id
     * @return stdClass
     */
    public function findOne(string $id): ?stdClass
    {
        $find = $this->find($id);
        if (!$find) {
            return null;
        }

        unset($find->password);
        unset($find->email_verified_at);
        unset($find->created_at);
        unset($find->updated_at);
        return $find;
    }

    /**
     * Import data users
     * @param UplodedFile $file
     * @return bool
     */
    public function import(UploadedFile $file): bool
    {
        try {
            Excel::import(new UserImport, $file);
        } catch (\Exception $e) {
            if (config('exo.log')) {
                Log::error($e->getMessage(), Error::get($e));
            }
            return false;
        }
        return true;
    }

    /**
     * Delete data users
     * @param array $user_ids
     * @return bool
     */
    public function deletes(array $user_ids): bool
    {
        $deleted = $this->repository->deletes($user_ids);
        if ($deleted->getErrors()) {
            if (config('exo.log')) {
                Log::error('error when deletes data', $deleted->getErrors());
            }
            return false;
        }
        return true;
    }
}