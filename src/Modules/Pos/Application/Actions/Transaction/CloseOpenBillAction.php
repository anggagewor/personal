<?php

namespace Modules\Pos\Application\Actions\Transaction;

use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Entities\Transaction;

class CloseOpenBillAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
    ) {}

    public function execute(
        int $transactionId,
        string $paymentMethod,
        string $paymentMethodType,
        ?float $amountTendered = null,
    ): Transaction {
        $transaction = $this->transactionRepo->findById($transactionId);

        if ($transaction === null) {
            throw new \RuntimeException("Open bill tidak ditemukan.");
        }

        if (!$transaction->isPending()) {
            throw new \DomainException("Hanya transaksi dengan status pending yang dapat ditutup.");
        }

        return $this->transactionRepo->closeOpenBill(
            $transactionId,
            $paymentMethod,
            $paymentMethodType,
            $amountTendered,
        );
    }
}
