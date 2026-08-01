<?php

namespace Modules\AuditLog\Application\Actions;

use Modules\AuditLog\Application\DTO\AuditLogQuery;
use Modules\AuditLog\Domain\Contracts\AuditLogDriverInterface;

class QueryAuditLogAction
{
    public function __construct(
        private AuditLogDriverInterface $driver,
    ) {}

    public function execute(AuditLogQuery $query): array
    {
        return $this->driver->query($query->toFilters(), $query->perPage);
    }
}
