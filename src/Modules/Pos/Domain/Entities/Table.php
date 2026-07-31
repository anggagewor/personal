<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;

class Table
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public string $name,
        public string $token,
        public bool $isActive = true,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
