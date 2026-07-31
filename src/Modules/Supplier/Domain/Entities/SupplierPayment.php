<?php

namespace Modules\Supplier\Domain\Entities;

use DateTimeImmutable;
use Modules\Supplier\Domain\Enums\PaymentMethod;

class SupplierPayment
{
    public function __construct(
        public ?int $id,
        public int $purchaseOrderId,
        public float $amount,
        public string $paymentDate,
        public PaymentMethod $paymentMethod,
        public ?string $notes = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
