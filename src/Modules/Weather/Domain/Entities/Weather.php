<?php

namespace Modules\__MODULE__\Domain\Entities;

use DateTimeImmutable;

class __MODULE__
{
    public function __construct(
        public ?int $id,
        public int $userId,
        // TODO: Add entity properties
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
