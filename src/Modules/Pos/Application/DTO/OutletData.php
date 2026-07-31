<?php

namespace Modules\Pos\Application\DTO;

use Modules\Pos\Domain\Enums\BusinessType;
use Modules\Pos\Domain\Enums\PaymentFlowMode;

readonly class OutletData
{
    public function __construct(
        public string $name,
        public BusinessType $businessType,
        public PaymentFlowMode $paymentFlow = PaymentFlowMode::PayFirst,
        public ?string $address = null,
        public ?string $phone = null,
        public ?array $settings = null,
    ) {}
}
