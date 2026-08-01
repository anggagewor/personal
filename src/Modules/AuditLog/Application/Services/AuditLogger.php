<?php

namespace Modules\AuditLog\Application\Services;

use Illuminate\Http\Request;
use Modules\AuditLog\Application\Actions\LogAuditAction;
use Modules\AuditLog\Application\DTO\AuditEntryData;
use Modules\AuditLog\Domain\Entities\AuditEntry;
use Modules\AuditLog\Domain\Enums\AuditEvent;

class AuditLogger
{
    public function __construct(
        private LogAuditAction $action,
        private ?Request $request = null,
    ) {}

    /**
     * Log an audit entry with automatic request context.
     */
    public function log(AuditEntryData $data, ?int $userId = null): AuditEntry
    {
        $userId = $userId ?? $this->request?->user()?->id;

        return $this->action->execute(
            userId: $userId,
            data: $data,
            url: $this->request?->fullUrl(),
            method: $this->request?->method(),
            ipAddress: $this->request?->ip(),
            userAgent: $this->request?->userAgent(),
        );
    }

    /**
     * Shorthand: log a "created" event.
     */
    public function created(string $auditableType, ?int $auditableId = null, ?array $newValues = null, ?string $tags = null, ?int $userId = null): AuditEntry
    {
        return $this->log(
            AuditEntryData::created($auditableType, $auditableId, $newValues, $tags),
            $userId,
        );
    }

    /**
     * Shorthand: log an "updated" event.
     */
    public function updated(string $auditableType, ?int $auditableId = null, ?array $oldValues = null, ?array $newValues = null, ?string $tags = null, ?int $userId = null): AuditEntry
    {
        return $this->log(
            AuditEntryData::updated($auditableType, $auditableId, $oldValues, $newValues, $tags),
            $userId,
        );
    }

    /**
     * Shorthand: log a "deleted" event.
     */
    public function deleted(string $auditableType, ?int $auditableId = null, ?array $oldValues = null, ?string $tags = null, ?int $userId = null): AuditEntry
    {
        return $this->log(
            AuditEntryData::deleted($auditableType, $auditableId, $oldValues, $tags),
            $userId,
        );
    }

    /**
     * Shorthand: log a custom event.
     */
    public function custom(string $event, string $auditableType, ?int $auditableId = null, ?array $metadata = null, ?string $tags = null, ?int $userId = null): AuditEntry
    {
        return $this->log(
            AuditEntryData::custom($event, $auditableType, $auditableId, $metadata, $tags),
            $userId,
        );
    }

    /**
     * Shorthand: log an auth event (login/logout).
     */
    public function auth(AuditEvent $event, ?int $userId = null, ?array $metadata = null): AuditEntry
    {
        $data = new AuditEntryData(
            event: $event,
            auditableType: 'user',
            auditableId: $userId,
            metadata: $metadata,
            tags: 'auth',
        );

        return $this->log($data, $userId);
    }
}
