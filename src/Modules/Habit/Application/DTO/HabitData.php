<?php

namespace Modules\Habit\Application\DTO;

class HabitData
{
    public function __construct(
        public readonly string $name,
        public readonly string $frequency = 'daily',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            frequency: $data['frequency'] ?? 'daily',
        );
    }
}
