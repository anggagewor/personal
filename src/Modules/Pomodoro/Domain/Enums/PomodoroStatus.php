<?php

namespace Modules\Pomodoro\Domain\Enums;

enum PomodoroStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Running => 'Running',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
