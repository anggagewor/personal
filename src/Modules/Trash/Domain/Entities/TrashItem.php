<?php

namespace Modules\Trash\Domain\Entities;

use DateTimeImmutable;

class TrashItem
{
    public function __construct(
        public int $id,
        public string $type,
        public string $title,
        public ?DateTimeImmutable $deletedAt = null,
    ) {}
}
