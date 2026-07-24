<?php

namespace Modules\Habit\Infrastructure\Resources;

use Modules\Habit\Domain\Entities\Habit;

class HabitResource
{
    public static function toArray(Habit $habit): array
    {
        return [
            'id' => $habit->id,
            'user_id' => $habit->userId,
            'name' => $habit->name,
            'frequency' => $habit->frequency,
            'current_streak' => $habit->currentStreak,
            'longest_streak' => $habit->longestStreak,
            'last_completed_at' => $habit->lastCompletedAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'created_at' => $habit->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'updated_at' => $habit->updatedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $habits): array
    {
        return array_map(fn (Habit $habit) => self::toArray($habit), $habits);
    }
}
