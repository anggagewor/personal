<?php

namespace Modules\AuditLog\Domain\Contracts;

use DateTimeImmutable;
use Modules\AuditLog\Domain\Entities\AuditEntry;

interface AuditLogDriverInterface
{
    public function log(AuditEntry $entry): AuditEntry;

    public function query(array $filters = [], int $perPage = 15): array;

    public function purge(DateTimeImmutable $before): int;
}
