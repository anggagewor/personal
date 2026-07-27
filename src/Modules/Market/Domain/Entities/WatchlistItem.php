<?php

namespace Modules\Market\Domain\Entities;

use DateTimeImmutable;

class WatchlistItem
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $symbol,
        public string $type,
        public ?string $label = null,
        public int $position = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
