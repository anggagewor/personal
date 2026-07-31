<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;

class Category
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public ?int $parentId = null,
        public string $name = '',
        public ?string $icon = null,
        public int $sortOrder = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
