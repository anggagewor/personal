<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\BusinessType;
use Modules\Pos\Domain\Enums\PaymentFlowMode;

class Outlet
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $name,
        public BusinessType $businessType,
        public PaymentFlowMode $paymentFlow = PaymentFlowMode::PayFirst,
        public ?string $address = null,
        public ?string $phone = null,
        public ?array $settings = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $deletedAt = null,
    ) {}

    public function supportsTableOrdering(): bool
    {
        return in_array($this->businessType, [BusinessType::Kafe, BusinessType::Warkop]);
    }

    public function supportsPayLater(): bool
    {
        return in_array($this->paymentFlow, [PaymentFlowMode::PayLater, PaymentFlowMode::Both]);
    }
}
