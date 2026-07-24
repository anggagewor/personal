<?php

namespace Modules\Goal\Infrastructure\Resources;

use Modules\Goal\Domain\Entities\Goal;

class GoalResource
{
    public static function toArray(Goal $goal): array
    {
        return [
            'id' => $goal->id,
            'user_id' => $goal->userId,
            'title' => $goal->title,
            'description' => $goal->description,
            'target_date' => $goal->targetDate?->format('Y-m-d'),
            'status' => $goal->status->value,
            'progress' => $goal->progress,
            'milestones' => $goal->milestones,
            'created_at' => $goal->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $goals): array
    {
        return array_map(fn (Goal $goal) => self::toArray($goal), $goals);
    }
}
