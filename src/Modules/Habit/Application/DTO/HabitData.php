<?php

namespace Modules\Habit\Application\DTO;

readonly class HabitData
{
    public function __construct(
        public string $name,
        public string $frequency = 'daily',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            frequency: $data['frequency'] ?? 'daily',
        );
    }
}
