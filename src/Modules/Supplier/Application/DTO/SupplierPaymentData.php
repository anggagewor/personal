<?php

namespace Modules\Supplier\Application\DTO;

use Modules\Supplier\Domain\Enums\PaymentMethod;

readonly class SupplierPaymentData
{
    public function __construct(
        public int $purchaseOrderId,
        public float $amount,
        public string $paymentDate,
        public PaymentMethod $paymentMethod,
        public ?string $notes,
    ) {}
}
