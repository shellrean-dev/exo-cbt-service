<?php declare(strict_types=1);

namespace ShellreanDev\Repositories\Jurusan;

use ShellreanDev\Repositories\AbstractRepository;
use Illuminate\Support\Facades\DB;
use ShellreanDev\Utils\Error;

/**
 * JurusanRepository repository
 * @author shellrean <wandinak17@gmail.com>
 */
final class JurusanRepository extends AbstractRepository
{
    /**
     * Table of repository
     * @var string
     */
    protected string $table = 'jurusans';

    protected bool $timestamps = false;

    /**
     * Get table 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Fetch all data
     * @return self
     */
    public function fetchAll(): self
    {
        try {
            $data = DB::table($this->table)
                ->orderBy('nama')
                ->get();

            $this->entities = $data;
        } catch (\Exception $e) {
            array_push($this->errors, Error::get($e));
        }
        return $this;
    }

    /**
     * Delete multiple data
     * @return self
     */
    public function deletes(array $ids): self
    {
        try {
            DB::table($this->table)
                ->whereIn('id', $ids)
                ->delete();

        } catch (\Exception $e) {
            array_push($this->errors, Error::get($e));
        }
        return $this;
    }
}