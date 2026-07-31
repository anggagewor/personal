<?php

namespace Modules\Supplier\Domain\Entities;

use DateTimeImmutable;

class Supplier
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public string $name,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $bankName = null,
        public ?string $bankAccountNumber = null,
        public ?string $bankAccountHolder = null,
        public ?string $notes = null,
        public float $totalDebt = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?DateTimeImmutable $deletedAt = null,
    ) {}
}
