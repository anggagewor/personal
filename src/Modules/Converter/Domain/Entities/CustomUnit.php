<?php

namespace Modules\Converter\Domain\Entities;

use DateTimeImmutable;

class CustomUnit
{
    public function __construct(
        public ?int $id,
        public int $categoryId,
        public string $name,
        public string $symbol,
        public float $toBase,
        public bool $isBase = false,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
