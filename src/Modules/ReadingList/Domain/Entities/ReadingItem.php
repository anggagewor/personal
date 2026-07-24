<?php

namespace Modules\ReadingList\Domain\Entities;

use DateTimeImmutable;

class ReadingItem
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public ?string $url = null,
        public ?string $domain = null,
        public bool $isRead = false,
        public bool $isFavorite = false,
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function toggleRead(): void
    {
        $this->isRead = !$this->isRead;
    }

    public function toggleFavorite(): void
    {
        $this->isFavorite = !$this->isFavorite;
    }
}
