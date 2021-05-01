<?php

declare(strict_types=1);

namespace ShellreanDev\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

/**
 * Abstract repositories
 * @author shellrean <wandinak17@gmail.com>
 * @year 2021
 */
class AbstractRepository implements RepositoryInterface
{
    /**
     * Table name for repository
     */
    protected string $table;

    /**
     * Primary key
     */
    protected string $primary_key = 'id';

    /**
     * Timestamps 
     */
    protected bool $timestamps = true;

    /**
     * Entity the object
     */
    protected ?stdClass $entity;

    /**
     * Entities the objects
     * @var 
     */
    protected ?iterable $entities;

    /**
     * Errors handle
     */
    protected array $errors = [];

    /**
     * Get primary key
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primary_key;
    }

    /**
     * Get errors data
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get entity data
     * @return stdClass
     */
    public function getEntity(): ?stdClass
    {
        return $this->entity;
    }

    /**
     * Get entities data
     * @return array
     */
    public function getEntities(): ?iterable
    {
        return $this->entities;
    }

    /**
     * Fetch data
     * @return self
     */
    public function fetch(int $limit = 10, int $offset = 0): self
    {
        try {
            $datas = DB::table($this->table)
                ->limit($limit)
                ->offset($offset)
                ->get();

            $this->entities = $datas;
        } catch (\Exception $e) {
            array_push($this->errors, $e->getMessage());
        }
        return $this;
    }

    /**
     * Get single data table
     * @param $id
     * @return self
     */
    public function findOne($id): self
    {
        try {
            $data = DB::table($this->table)
                ->where($this->primary_key, $id)
                ->first();
            
            $this->entity = $data;
        } catch (\Exception $e) {
            array_push($this->errors, $e->getMessage());
        }
        return $this;
    }

    /**
     * Store data to table repository
     * @param stdClass $data
     * @return self
     */
    public function store(stdClass $data): self
    {
        try {
            $data->{$this->primary_key} = Str::uuid()->toString();

            if ($this->timestamps) {
                $data->created_at = now();
                $data->updated_at = now();
            }

            DB::table($this->table)
                ->insert((array) $data);
            
            $this->entity = $data;
        } catch (\Exception $e) {
            array_push($this->errors, $e->getMessage());
        }

        return $this;
    }

    /**
     * Update data from table repository
     * @param stdClass $data
     * @return self
     */
    public function update(stdClass $data): self
    {
        try {
            if ($this->timestamps) {
                $data->updated_at = now();
            }

            DB::table($this->table)
                ->where($this->primary_key, $data->{$this->primary_key})
                ->update((array) $data);

            $this->entity = $data;
        } catch (\Exception $e) {
            array_push($this->errors, $e->getMessage());
        }

        return $this;
    }

    /**
     * Delete data from table repository
     * @param $id
     * @return bool
     */
    public function destroy($id): self
    {
        try {
            DB::table($this->table)
                ->where($this->primary_key, $id)
                ->delete();
        } catch (\Exception $e) {
            array_push($this->errors, $e->getMessage());
        }

        return $this;
    }
}