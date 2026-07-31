<?php

namespace Modules\Pos\Application\Actions\Transaction;

use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Entities\Transaction;
use Modules\Pos\Domain\Enums\TransactionStatus;
use Modules\Pos\Domain\Exceptions\VoidNotAllowedException;

class VoidTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(int $transactionId, string $reason): Transaction
    {
        $transaction = $this->transactionRepo->findById($transactionId);

        if ($transaction === null) {
            throw new \RuntimeException("Transaksi tidak ditemukan.");
        }

        // Validate void rules
        if ($transaction->isVoided()) {
            throw VoidNotAllowedException::alreadyVoided($transaction->transactionNumber);
        }

        if ($transaction->status !== TransactionStatus::Completed) {
            throw VoidNotAllowedException::notCompleted($transaction->transactionNumber);
        }

        // Void the transaction via repository
        $voidedTransaction = $this->transactionRepo->void($transactionId, $reason);

        // Restore stock for each item where product has stock tracking enabled
        foreach ($transaction->items as $item) {
            $product = $this->productRepo->findById($item->productId);

            if ($product && $product->trackStock) {
                $variantId = $item->productVariantId ?? $this->getDefaultVariantId($product);

                if ($variantId) {
                    $this->productRepo->adjustStock(
                        $variantId,
                        $item->quantity,
                        'void',
                        'Void: ' . $reason,
                    );
                }
            }
        }

        return $voidedTransaction;
    }

    private function getDefaultVariantId($product): ?int
    {
        if (empty($product->variants)) {
            return null;
        }

        return $product->variants[0]->id;
    }
}
