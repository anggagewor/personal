<?php

namespace Modules\AuditLog\Application\Actions;

use DateTimeImmutable;
use Modules\AuditLog\Domain\Contracts\AuditLogDriverInterface;

class PurgeAuditLogAction
{
    public function __construct(
        private AuditLogDriverInterface $driver,
    ) {}

    /**
     * Purge audit log entries older than the given date.
     *
     * @return int Number of entries purged
     */
    public function execute(DateTimeImmutable $before): int
    {
        return $this->driver->purge($before);
    }
}
