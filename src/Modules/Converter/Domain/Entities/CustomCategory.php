<?php

namespace Modules\Converter\Domain\Entities;

use DateTimeImmutable;

class CustomCategory
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public ?string $description = null,
        public ?string $icon = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
