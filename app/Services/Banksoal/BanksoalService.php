<?php declare(strict_types=1);

namespace ShellreanDev\Services\Banksoal;

use Illuminate\Support\Facades\Log;
use ShellreanDev\Cache\CacheHandler;
use ShellreanDev\Services\AbstractService;

/**
 * BanksoalService service
 * @author shellrean <wandinak17@gmail.com>
 */
final class BanksoalService extends AbstractService
{
    /**
     * Dependency injection
     */
    public function __construct(CacheHandler $cache)
    {
        $this->cache = $cache;
    }
}