<?php

namespace Modules\Goal\Domain\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Abandoned => 'Abandoned',
        };
    }
}
