<?php

declare(strict_types=1);

namespace ShellreanDev\Repositories;

use stdClass;

/**
 * Repository interface
 * @author shellrean <wandinak17@gmail.com>
 */
interface RepositoryInterface
{
    /**
     * Get primary key of repository
     * @return string
     */
    public function getPrimaryKey(): ?string;

    /**
     * Get errors on repository operations
     * @return array
     */
    public function getErrors(): ?array;

    /**
     * Get entities of repository
     * @return array
     */
    public function getEntities(): ?iterable;

    /**
     * Get entity of repository
     * @return stdClass
     */
    public function getEntity(): ?stdClass;

    /**
     * Fetch data from repository
     * @param $limit
     * @param $offfset
     * @return self
     */
    public function fetch(int $limit, int $offset): self;

    /**
     * Find single data from repository
     * @param $key
     * @return self
     */
    public function findOne($key): self;

    /**
     * Store data to repository
     * @param stdClass $data
     * @return self
     */
    public function store(stdClass $data): self;

    /**
     * Update data in repository
     * @param stdClass $data
     * @return self
     */
    public function update(stdClass $data): self;

    /**
     * Destroy data in repository
     * @param $id
     * @return self
     */
    public function destroy($id): self;
}