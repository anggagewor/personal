<?php

namespace Modules\AuditLog\Application\DTO;

use Modules\AuditLog\Domain\Enums\AuditEvent;

readonly class AuditLogQuery
{
    public function __construct(
        public ?int $userId = null,
        public ?AuditEvent $event = null,
        public ?string $auditableType = null,
        public ?int $auditableId = null,
        public ?string $tags = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $perPage = 15,
    ) {}

    public function toFilters(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'event' => $this->event?->value,
            'auditable_type' => $this->auditableType,
            'auditable_id' => $this->auditableId,
            'tags' => $this->tags,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ], fn ($value) => $value !== null);
    }
}
