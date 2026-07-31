<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\PaymentMethodType;

class PaymentMethod
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public PaymentMethodType $type,
        public string $name,
        public bool $isActive = true,
        public ?array $settings = null,
        public int $sortOrder = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
