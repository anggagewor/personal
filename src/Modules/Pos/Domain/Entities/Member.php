<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;

class Member
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public string $name,
        public string $phone,
        public ?string $email = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
