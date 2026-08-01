<?php

namespace Modules\AuditLog\Domain\Contracts;

use DateTimeImmutable;
use Modules\AuditLog\Domain\Entities\AuditEntry;

interface AuditLogRepositoryInterface
{
    public function save(AuditEntry $entry): AuditEntry;

    public function findByAuditable(string $auditableType, int $auditableId, int $perPage = 15): array;

    public function findByUser(int $userId, int $perPage = 15): array;

    public function findByFilters(array $filters = [], int $perPage = 15): array;

    public function purge(DateTimeImmutable $before): int;
}
