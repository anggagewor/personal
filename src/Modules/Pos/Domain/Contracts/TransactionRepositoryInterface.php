<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\CheckoutData;
use Modules\Pos\Domain\Entities\Refund;
use Modules\Pos\Domain\Entities\Transaction;

interface TransactionRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array;

    public function findById(int $id): ?Transaction;

    public function create(int $outletId, CheckoutData $data): Transaction;

    public function void(int $id, string $reason): Transaction;

    public function generateTransactionNumber(int $outletId): string;

    public function findOpenBillsByOutlet(int $outletId): array;

    public function closeOpenBill(int $id, string $paymentMethod, string $paymentMethodType, ?float $amountTendered): Transaction;

    public function createRefund(int $transactionId, string $refundNumber, float $refundAmount, string $reason, ?string $refundMethod, array $items): Refund;

    public function updateRefundedAmount(int $transactionId, float $totalRefunded): void;

    public function updateStatus(int $transactionId, string $status): void;

    public function getRefundedQuantityByItem(int $transactionItemId): int;

    public function findRefundsByTransaction(int $transactionId): array;
}
