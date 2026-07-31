<?php

namespace Modules\Pos\Application\DTO;

readonly class CheckoutData
{
    public function __construct(
        public int $outletId,
        /** @var LineItemData[] */
        public array $items,
        public ?string $paymentMethod = null,
        public ?string $paymentMethodType = null,
        public ?float $amountTendered = null,
        public ?int $memberId = null,
        public ?string $voucherCode = null,
        public ?string $notes = null,
        public ?string $status = null,
        public float $discountAmount = 0,
        /** @var array{discount_id: int|null, name: string, type: string, value: float, amount: float}[] */
        public array $appliedDiscounts = [],
    ) {}
}
