<?php

namespace Modules\Pos\Application\DTO;

readonly class VoucherData
{
    public function __construct(
        public string $code,
        public string $type,
        public float $value,
        public ?float $minPurchase = null,
        public ?int $usageLimit = null,
        public ?string $expiresAt = null,
        public bool $isActive = true,
    ) {}
}
