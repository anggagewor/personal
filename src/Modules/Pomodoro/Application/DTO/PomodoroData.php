<?php

namespace Modules\Pomodoro\Application\DTO;

readonly class PomodoroData
{
    public function __construct(
        public ?int $taskId = null,
        public int $duration = 25,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            taskId: $data['task_id'] ?? null,
            duration: $data['duration'] ?? 25,
        );
    }
}
