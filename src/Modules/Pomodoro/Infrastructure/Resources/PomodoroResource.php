<?php

namespace Modules\Pomodoro\Infrastructure\Resources;

use Modules\Pomodoro\Domain\Entities\Pomodoro;

class PomodoroResource
{
    public static function toArray(Pomodoro $pomodoro): array
    {
        return [
            'id' => $pomodoro->id,
            'user_id' => $pomodoro->userId,
            'task_id' => $pomodoro->taskId,
            'duration' => $pomodoro->duration,
            'status' => $pomodoro->status->value,
            'started_at' => $pomodoro->startedAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'completed_at' => $pomodoro->completedAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $pomodoros): array
    {
        return array_map(fn (Pomodoro $pomodoro) => self::toArray($pomodoro), $pomodoros);
    }
}
