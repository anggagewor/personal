<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\TransactionStatus;

class Transaction
{
    public function __construct(
        public ?int $id,
        public int $outletId,
        public string $transactionNumber,
        public float $subtotal,
        public float $discountAmount,
        public float $taxRate = 0,
        public float $taxAmount = 0,
        public bool $taxInclusive = false,
        public float $total,
        public float $refundedAmount = 0,
        public ?string $paymentMethod = null,
        public ?string $paymentMethodType = null,
        public ?float $amountTendered = null,
        public ?float $changeAmount = null,
        public TransactionStatus $status = TransactionStatus::Completed,
        public ?string $voidReason = null,
        public ?int $memberId = null,
        public ?int $tableSessionId = null,
        public ?string $voucherCode = null,
        /** @var TransactionItem[] */
        public array $items = [],
        /** @var array{name: string, type: string, value: float, amount: float}[] */
        public array $appliedDiscounts = [],
        public ?string $notes = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $voidedAt = null,
    ) {}

    public function isVoided(): bool
    {
        return $this->status === TransactionStatus::Voided;
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::Pending;
    }

    public function isOverdue(): bool
    {
        if (!$this->isPending() || $this->createdAt === null) {
            return false;
        }

        $threshold = new DateTimeImmutable('-24 hours');

        return $this->createdAt < $threshold;
    }

    public function canVoidWithoutConfirmation(): bool
    {
        if ($this->createdAt === null) {
            return true;
        }

        $threshold = new DateTimeImmutable('-24 hours');

        return $this->createdAt >= $threshold;
    }

    public function isLinkedToMember(): bool
    {
        return $this->memberId !== null;
    }

    public function isRefunded(): bool
    {
        return $this->status === TransactionStatus::Refunded;
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->status === TransactionStatus::PartiallyRefunded;
    }

    public function canBeRefunded(): bool
    {
        return in_array($this->status, [TransactionStatus::Completed, TransactionStatus::PartiallyRefunded], true);
    }

    public function getRefundableAmount(): float
    {
        return max(0, $this->total - $this->refundedAmount);
    }
}
