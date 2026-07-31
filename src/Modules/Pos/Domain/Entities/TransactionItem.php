<?php

namespace Modules\Pos\Domain\Entities;

class TransactionItem
{
    public function __construct(
        public ?int $id,
        public int $transactionId,
        public int $productId,
        public ?int $productVariantId = null,
        public string $productName = '',
        public ?string $variantName = null,
        public int $quantity = 1,
        public float $unitPrice = 0,
        public float $subtotal = 0,
    ) {}
}
