<?php

namespace Modules\Supplier\Domain\Entities;

class PurchaseOrderItem
{
    public function __construct(
        public ?int $id,
        public int $purchaseOrderId,
        public int $productVariantId,
        public string $productName,
        public string $variantName,
        public int $quantity,
        public float $unitCost,
        public float $subtotal,
        public int $receivedQuantity = 0,
    ) {}

    public function getRemainingQuantity(): int
    {
        return $this->quantity - $this->receivedQuantity;
    }

    public function isFullyReceived(): bool
    {
        return $this->receivedQuantity >= $this->quantity;
    }
}
