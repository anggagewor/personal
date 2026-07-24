<?php

namespace Modules\Activity\Infrastructure\Resources;

use Modules\Activity\Domain\Entities\ActivityLog;

class ActivityLogResource
{
    public static function toArray(ActivityLog $log): array
    {
        return [
            'id' => $log->id,
            'user_id' => $log->userId,
            'action' => $log->action,
            'description' => $log->description,
            'metadata' => $log->metadata,
            'created_at' => $log->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $logs): array
    {
        return array_map(fn (ActivityLog $log) => self::toArray($log), $logs);
    }
}
