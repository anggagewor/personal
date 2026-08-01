<?php

namespace Modules\AuditLog\Application\Actions;

use Modules\AuditLog\Application\DTO\AuditEntryData;
use Modules\AuditLog\Domain\Contracts\AuditLogDriverInterface;
use Modules\AuditLog\Domain\Entities\AuditEntry;

class LogAuditAction
{
    public function __construct(
        private AuditLogDriverInterface $driver,
    ) {}

    public function execute(?int $userId, AuditEntryData $data, ?string $url = null, ?string $method = null, ?string $ipAddress = null, ?string $userAgent = null): AuditEntry
    {
        $entry = new AuditEntry(
            id: null,
            userId: $userId,
            event: $data->event,
            auditableType: $data->auditableType,
            auditableId: $data->auditableId,
            oldValues: $data->oldValues,
            newValues: $data->newValues,
            url: $url,
            method: $method,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            tags: $data->tags,
            metadata: $data->metadata,
        );

        return $this->driver->log($entry);
    }
}
