<?php

namespace Modules\AuditLog\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\AuditLog\Domain\Contracts\AuditLogRepositoryInterface;
use Modules\AuditLog\Domain\Entities\AuditEntry;
use Modules\AuditLog\Domain\Enums\AuditEvent;
use Modules\AuditLog\Infrastructure\Models\AuditLogModel;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function save(AuditEntry $entry): AuditEntry
    {
        $model = AuditLogModel::create([
            'user_id' => $entry->userId,
            'event' => $entry->event->value,
            'auditable_type' => $entry->auditableType,
            'auditable_id' => $entry->auditableId,
            'old_values' => $entry->oldValues,
            'new_values' => $entry->newValues,
            'url' => $entry->url,
            'method' => $entry->method,
            'ip_address' => $entry->ipAddress,
            'user_agent' => $entry->userAgent,
            'tags' => $entry->tags,
            'metadata' => $entry->metadata,
            'created_at' => now(),
        ]);

        return $this->toEntity($model);
    }

    public function findByAuditable(string $auditableType, int $auditableId, int $perPage = 15): array
    {
        $paginator = AuditLogModel::where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function findByUser(int $userId, int $perPage = 15): array
    {
        $paginator = AuditLogModel::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function findByFilters(array $filters = [], int $perPage = 15): array
    {
        $query = AuditLogModel::query();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (isset($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (isset($filters['auditable_id'])) {
            $query->where('auditable_id', $filters['auditable_id']);
        }

        if (isset($filters['tags'])) {
            $query->where('tags', $filters['tags']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function purge(DateTimeImmutable $before): int
    {
        return AuditLogModel::where('created_at', '<', $before->format('Y-m-d H:i:s'))->delete();
    }

    private function toEntity(AuditLogModel $model): AuditEntry
    {
        return new AuditEntry(
            id: $model->id,
            userId: $model->user_id,
            event: AuditEvent::from($model->event),
            auditableType: $model->auditable_type,
            auditableId: $model->auditable_id,
            oldValues: $model->old_values,
            newValues: $model->new_values,
            url: $model->url,
            method: $model->method,
            ipAddress: $model->ip_address,
            userAgent: $model->user_agent,
            tags: $model->tags,
            metadata: $model->metadata,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
