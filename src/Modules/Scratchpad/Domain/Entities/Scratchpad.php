<?php

namespace Modules\Scratchpad\Domain\Entities;

use DateTimeImmutable;

class Scratchpad
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public ?string $content = null,
        public ?string $color = null,
        public int $position = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
