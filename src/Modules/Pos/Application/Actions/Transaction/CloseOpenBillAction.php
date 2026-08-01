<?php

namespace Modules\Pos\Application\Actions\Transaction;

use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Entities\Transaction;
use Modules\Pos\Domain\Exceptions\OpenBillException;

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
            throw OpenBillException::notFound($transactionId);
        }

        if ($transaction->isVoided()) {
            throw OpenBillException::alreadyClosed($transaction->transactionNumber);
        }

        if (!$transaction->isPending()) {
            throw OpenBillException::notPending($transaction->transactionNumber);
        }

        return $this->transactionRepo->closeOpenBill(
            $transactionId,
            $paymentMethod,
            $paymentMethodType,
            $amountTendered,
        );
    }
}
