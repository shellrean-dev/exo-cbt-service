<?php declare(strict_types=1);

namespace ShellreanDev\Repositories\User;

use ShellreanDev\Repositories\AbstractRepository;
use ShellreanDev\Utils\Error;

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
    protected string $table = '';
}