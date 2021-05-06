<?php declare(strict_types=1);

namespace ShellreanDev\Repositories\User;

use ShellreanDev\Repositories\AbstractRepository;
use ShellreanDev\Utils\Error;

use Illuminate\Support\Facades\DB;

/**
 * UserRepository repository
 * @author shellrean <wandinak17@gmail.com>
 */
final class UserRepository extends AbstractRepository
{
    /**
     * Table of repository
     * @var string $table
     */
    protected string $table = 'users';

    /**
     * Get repository's table
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Delete multiple data
     * @param array $user_ids
     * @return self
     */
    public function deletes(array $user_ids): self
    {
        try {
            DB::table($this->table)
                ->whereIn($this->primary_key, $user_ids)
                ->delete();
        } catch(\Exception $e) {
            array_push($this->errors, Error::get($e));
        }
        return $this;
    }
}