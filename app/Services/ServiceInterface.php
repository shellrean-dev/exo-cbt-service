<?php

declare(strict_types=1);

namespace ShellreanDev\Services;

use stdClass;

/**
 * Service interface
 * @author shellrean <wandinak17@gmail.com>
 */
interface ServiceInterface
{
    /**
     * Fetach data from repository
     * @param int $limit
     * @param int $offset
     */
    public function fetch(int $limit, int $offset);

    /**
     * Store data to repository
     * @param stdClass $data
     * @return self
     */
    public function store(stdClass $data): ?stdClass;

    /**
     * Update data to repository
     * @param stdClass $data
     * @return self
     */
    public function update(stdClass $data): ?stdClass;
    
    /**
     * Find data from repository
     * @param mixed $id
     * @return self
     */
    public function find(mixed $id): ?stdClass;

    /**
     * Destroy data from repository
     * @param mixed $id
     * @return self
     */
    public function destroy(mixed $id): bool;
}