<?php

namespace Modules\AuditLog\Application\DTO;

use Modules\AuditLog\Domain\Enums\AuditEvent;

readonly class AuditEntryData
{
    public function __construct(
        public AuditEvent $event,
        public string $auditableType,
        public ?int $auditableId = null,
        public ?array $oldValues = null,
        public ?array $newValues = null,
        public ?string $tags = null,
        public ?array $metadata = null,
    ) {}

    public static function created(string $auditableType, ?int $auditableId = null, ?array $newValues = null, ?string $tags = null): self
    {
        return new self(
            event: AuditEvent::Created,
            auditableType: $auditableType,
            auditableId: $auditableId,
            newValues: $newValues,
            tags: $tags,
        );
    }

    public static function updated(string $auditableType, ?int $auditableId = null, ?array $oldValues = null, ?array $newValues = null, ?string $tags = null): self
    {
        return new self(
            event: AuditEvent::Updated,
            auditableType: $auditableType,
            auditableId: $auditableId,
            oldValues: $oldValues,
            newValues: $newValues,
            tags: $tags,
        );
    }

    public static function deleted(string $auditableType, ?int $auditableId = null, ?array $oldValues = null, ?string $tags = null): self
    {
        return new self(
            event: AuditEvent::Deleted,
            auditableType: $auditableType,
            auditableId: $auditableId,
            oldValues: $oldValues,
            tags: $tags,
        );
    }

    public static function custom(string $event, string $auditableType, ?int $auditableId = null, ?array $metadata = null, ?string $tags = null): self
    {
        return new self(
            event: AuditEvent::Custom,
            auditableType: $auditableType,
            auditableId: $auditableId,
            metadata: array_merge($metadata ?? [], ['custom_event' => $event]),
            tags: $tags,
        );
    }
}
