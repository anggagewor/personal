<?php

namespace Modules\Bookmark\Domain\Entities;

use DateTimeImmutable;

class Bookmark
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public string $url,
        public ?string $description = null,
        public ?string $category = null,
        public ?string $icon = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
