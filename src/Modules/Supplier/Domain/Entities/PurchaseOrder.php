<?php

namespace Modules\Supplier\Domain\Entities;

use DateTimeImmutable;
use Modules\Supplier\Domain\Enums\PaymentStatus;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;

class PurchaseOrder
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public int $supplierId,
        public string $poNumber,
        public string $orderDate,
        public ?string $expectedDeliveryDate = null,
        public PurchaseOrderStatus $status = PurchaseOrderStatus::Draft,
        public PaymentStatus $paymentStatus = PaymentStatus::Unpaid,
        public float $totalAmount = 0,
        public ?string $notes = null,
        public ?DateTimeImmutable $cancelledAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        /** @var PurchaseOrderItem[] */
        public array $items = [],
    ) {}

    public function isDraft(): bool
    {
        return $this->status === PurchaseOrderStatus::Draft;
    }

    public function isEditable(): bool
    {
        return $this->status === PurchaseOrderStatus::Draft;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            PurchaseOrderStatus::Draft,
            PurchaseOrderStatus::Confirmed,
        ]);
    }

    public function canReceiveGoods(): bool
    {
        return in_array($this->status, [
            PurchaseOrderStatus::Confirmed,
            PurchaseOrderStatus::Partial,
        ]);
    }
}
