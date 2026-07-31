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
        public float $total,
        public ?string $paymentMethod = null,
        public ?float $amountTendered = null,
        public ?float $changeAmount = null,
        public TransactionStatus $status = TransactionStatus::Completed,
        public ?string $voidReason = null,
        public ?int $memberId = null,
        public ?int $tableSessionId = null,
        public ?string $voucherCode = null,
        /** @var TransactionItem[] */
        public array $items = [],
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
}
