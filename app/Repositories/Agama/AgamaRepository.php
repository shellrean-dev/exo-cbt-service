<?php

declare(strict_types=1);

namespace ShellreanDev\Repositories\Agama;

use ShellreanDev\Repositories\AbstractRepository;
use Illuminate\Support\Facades\DB;
use ShellreanDev\Utils\Error;

/**
 * Agama repository
 * @author shellrean <wandinak17@gmail.com>
 */
final class AgamaRepository extends AbstractRepository
{
    /**
     * Table of repository
     * @var string
     */
    protected string $table = 'agamas';

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
}