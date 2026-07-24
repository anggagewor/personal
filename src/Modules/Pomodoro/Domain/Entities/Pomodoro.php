<?php

namespace Modules\Pomodoro\Domain\Entities;

use DateTimeImmutable;
use Modules\Pomodoro\Domain\Enums\PomodoroStatus;

class Pomodoro
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public ?int $taskId = null,
        public int $duration = 25,
        public PomodoroStatus $status = PomodoroStatus::Running,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $completedAt = null,
    ) {}

    public function complete(): void
    {
        $this->status = PomodoroStatus::Completed;
        $this->completedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = PomodoroStatus::Cancelled;
    }
}
