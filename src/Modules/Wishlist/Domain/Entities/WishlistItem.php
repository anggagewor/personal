<?php

namespace Modules\Wishlist\Domain\Entities;

use DateTimeImmutable;

class WishlistItem
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public ?string $description = null,
        public ?string $category = null,
        public bool $isCompleted = false,
        public ?DateTimeImmutable $completedAt = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function toggle(): void
    {
        $this->isCompleted = !$this->isCompleted;
        $this->completedAt = $this->isCompleted ? new DateTimeImmutable() : null;
    }
}
