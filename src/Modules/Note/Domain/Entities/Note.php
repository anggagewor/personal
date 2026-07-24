<?php

namespace Modules\Note\Domain\Entities;

use DateTimeImmutable;

class Note
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public string $content,
        public bool $isPinned = false,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?DateTimeImmutable $deletedAt = null,
    ) {}

    public function pin(): void
    {
        $this->isPinned = true;
    }

    public function unpin(): void
    {
        $this->isPinned = false;
    }

    public function togglePin(): void
    {
        $this->isPinned = !$this->isPinned;
    }
}
