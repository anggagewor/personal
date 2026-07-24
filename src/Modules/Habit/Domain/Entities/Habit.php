<?php

namespace Modules\Habit\Domain\Entities;

use DateTimeImmutable;

class Habit
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public string $frequency = 'daily',
        public int $currentStreak = 0,
        public int $longestStreak = 0,
        public ?DateTimeImmutable $lastCompletedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    public function toggle(): void
    {
        $today = new DateTimeImmutable('today');

        if ($this->lastCompletedAt && $this->lastCompletedAt->format('Y-m-d') === $today->format('Y-m-d')) {
            $this->currentStreak = max(0, $this->currentStreak - 1);
            $this->lastCompletedAt = null;
        } else {
            $this->currentStreak++;
            if ($this->currentStreak > $this->longestStreak) {
                $this->longestStreak = $this->currentStreak;
            }
            $this->lastCompletedAt = $today;
        }
    }
}
