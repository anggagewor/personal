<?php

namespace Modules\Journal\Domain\Enums;

enum JournalMood: string
{
    case Happy = 'happy';
    case Neutral = 'neutral';
    case Sad = 'sad';
    case Excited = 'excited';
    case Anxious = 'anxious';

    public function label(): string
    {
        return match ($this) {
            self::Happy => 'Happy',
            self::Neutral => 'Neutral',
            self::Sad => 'Sad',
            self::Excited => 'Excited',
            self::Anxious => 'Anxious',
        };
    }
}
