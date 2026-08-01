<?php

namespace Modules\AuditLog\Domain\Entities;

use DateTimeImmutable;
use Modules\AuditLog\Domain\Enums\AuditEvent;

class AuditEntry
{
    public function __construct(
        public ?int $id,
        public ?int $userId,
        public AuditEvent $event,
        public string $auditableType,
        public ?int $auditableId,
        public ?array $oldValues,
        public ?array $newValues,
        public ?string $url,
        public ?string $method,
        public ?string $ipAddress,
        public ?string $userAgent,
        public ?string $tags,
        public ?array $metadata,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function changedFields(): array
    {
        if ($this->oldValues === null || $this->newValues === null) {
            return [];
        }

        $changed = [];

        foreach ($this->newValues as $key => $newValue) {
            $oldValue = $this->oldValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changed;
    }
}
