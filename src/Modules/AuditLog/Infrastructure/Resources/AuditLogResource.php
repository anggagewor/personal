<?php

namespace Modules\AuditLog\Infrastructure\Resources;

use Modules\AuditLog\Domain\Entities\AuditEntry;

class AuditLogResource
{
    public static function toArray(AuditEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'user_id' => $entry->userId,
            'event' => $entry->event->value,
            'auditable_type' => $entry->auditableType,
            'auditable_id' => $entry->auditableId,
            'old_values' => $entry->oldValues,
            'new_values' => $entry->newValues,
            'changed_fields' => $entry->changedFields(),
            'url' => $entry->url,
            'method' => $entry->method,
            'ip_address' => $entry->ipAddress,
            'user_agent' => $entry->userAgent,
            'tags' => $entry->tags,
            'metadata' => $entry->metadata,
            'created_at' => $entry->createdAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param AuditEntry[] $entries
     */
    public static function collection(array $entries): array
    {
        return array_map(fn (AuditEntry $entry) => self::toArray($entry), $entries);
    }
}
