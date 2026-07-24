<?php

namespace Modules\Pomodoro\Application\DTO;

class PomodoroData
{
    public function __construct(
        public readonly ?int $taskId = null,
        public readonly int $duration = 25,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            taskId: $data['task_id'] ?? null,
            duration: $data['duration'] ?? 25,
        );
    }
}
