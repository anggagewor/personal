<?php

namespace Modules\Pos\Application\DTO;

readonly class DiscountData
{
    public function __construct(
        public string $name,
        public string $type,
        public float $value,
        public ?float $minPurchase = null,
        public bool $memberOnly = false,
        public bool $isActive = true,
        public int $priority = 0,
        public ?string $startsAt = null,
        public ?string $endsAt = null,
        public ?array $conditions = null,
    ) {}
}
