<?php

namespace Modules\Pos\Application\Actions\Transaction;

use Illuminate\Support\Carbon;
use Modules\Pos\Application\DTO\RefundData;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Entities\Refund;
use Modules\Pos\Domain\Enums\TransactionStatus;
use Modules\Pos\Domain\Exceptions\RefundNotAllowedException;

class RefundTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(RefundData $data): Refund
    {
        $transaction = $this->transactionRepo->findById($data->transactionId);

        if ($transaction === null) {
            throw new \RuntimeException("Transaksi tidak ditemukan.");
        }

        // Only completed or partially_refunded transactions can be refunded
        if (!in_array($transaction->status, [TransactionStatus::Completed, TransactionStatus::PartiallyRefunded], true)) {
            throw RefundNotAllowedException::notCompleted($transaction->transactionNumber);
        }

        if (empty($data->items)) {
            throw RefundNotAllowedException::emptyItems();
        }

        // Calculate refundable amount (total - already refunded)
        $currentRefunded = $this->getCurrentRefundedAmount($data->transactionId);
        $refundableAmount = $transaction->total - $currentRefunded;

        if ($refundableAmount <= 0) {
            throw RefundNotAllowedException::alreadyFullyRefunded($transaction->transactionNumber);
        }

        // Validate each item and calculate refund amount
        $refundItems = [];
        $totalRefundAmount = 0.0;

        foreach ($data->items as $refundItemData) {
            $transactionItem = $this->findTransactionItem($transaction, $refundItemData->transactionItemId);

            if ($transactionItem === null) {
                throw new \RuntimeException("Item transaksi tidak ditemukan.");
            }

            // Check available quantity (original - already refunded)
            $alreadyRefundedQty = $this->transactionRepo->getRefundedQuantityByItem($refundItemData->transactionItemId);
            $availableQty = $transactionItem->quantity - $alreadyRefundedQty;

            if ($refundItemData->quantity > $availableQty) {
                throw RefundNotAllowedException::itemQuantityExceedsAvailable(
                    $transactionItem->productName,
                    $refundItemData->quantity,
                    $availableQty,
                );
            }

            // Pro-rata refund: calculate proportional amount including discount
            $itemRefundAmount = $this->calculateItemRefundAmount(
                $transactionItem,
                $refundItemData->quantity,
                $transaction,
            );

            $totalRefundAmount += $itemRefundAmount;

            $refundItems[] = [
                'transaction_item_id' => $transactionItem->id,
                'product_id' => $transactionItem->productId,
                'product_variant_id' => $transactionItem->productVariantId,
                'product_name' => $transactionItem->productName,
                'variant_name' => $transactionItem->variantName,
                'quantity' => $refundItemData->quantity,
                'unit_price' => $transactionItem->unitPrice,
                'refund_amount' => round($itemRefundAmount, 2),
            ];
        }

        // Check that total refund doesn't exceed refundable amount
        $totalRefundAmount = round($totalRefundAmount, 2);
        if ($totalRefundAmount > $refundableAmount) {
            throw RefundNotAllowedException::exceedsRefundableAmount(
                $transaction->transactionNumber,
                $totalRefundAmount,
                $refundableAmount,
            );
        }

        // Generate refund number
        $refundNumber = $this->generateRefundNumber($transaction->outletId);

        // Create refund record
        $refund = $this->transactionRepo->createRefund(
            transactionId: $data->transactionId,
            refundNumber: $refundNumber,
            refundAmount: $totalRefundAmount,
            reason: $data->reason,
            refundMethod: $data->refundMethod,
            items: $refundItems,
        );

        // Update transaction's refunded_amount and status
        $newTotalRefunded = $currentRefunded + $totalRefundAmount;
        $this->transactionRepo->updateRefundedAmount($data->transactionId, $newTotalRefunded);

        // Determine new status
        $newStatus = $newTotalRefunded >= $transaction->total
            ? TransactionStatus::Refunded->value
            : TransactionStatus::PartiallyRefunded->value;
        $this->transactionRepo->updateStatus($data->transactionId, $newStatus);

        // Restore stock for refunded items
        foreach ($refundItems as $item) {
            $product = $this->productRepo->findById($item['product_id']);

            if ($product && $product->trackStock) {
                $variantId = $item['product_variant_id'] ?? $this->getDefaultVariantId($product);

                if ($variantId) {
                    $this->productRepo->adjustStock(
                        $variantId,
                        $item['quantity'],
                        'refund',
                        'Refund: ' . $data->reason,
                    );
                }
            }
        }

        return $refund;
    }

    private function findTransactionItem($transaction, int $itemId): ?\Modules\Pos\Domain\Entities\TransactionItem
    {
        foreach ($transaction->items as $item) {
            if ($item->id === $itemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Calculate the refund amount for a specific item quantity, applying pro-rata discount.
     * Formula: (quantity * unit_price) * (transaction.total / transaction.subtotal)
     * This distributes any discounts proportionally.
     */
    private function calculateItemRefundAmount(
        \Modules\Pos\Domain\Entities\TransactionItem $item,
        int $quantity,
        \Modules\Pos\Domain\Entities\Transaction $transaction,
    ): float {
        $itemSubtotal = $quantity * $item->unitPrice;

        // Calculate the ratio of total to subtotal to account for discounts and tax
        if ($transaction->subtotal > 0) {
            $ratio = $transaction->total / $transaction->subtotal;
        } else {
            $ratio = 1.0;
        }

        return $itemSubtotal * $ratio;
    }

    private function getCurrentRefundedAmount(int $transactionId): float
    {
        $refunds = $this->transactionRepo->findRefundsByTransaction($transactionId);
        $total = 0.0;
        foreach ($refunds as $refund) {
            $total += $refund->refundAmount;
        }

        return $total;
    }

    private function generateRefundNumber(int $outletId): string
    {
        $today = Carbon::today();
        $prefix = 'RFD-' . $today->format('ymd') . '-';

        $lastRefund = \Modules\Pos\Infrastructure\Models\RefundModel::where('refund_number', 'like', $prefix . '%')
            ->orderByDesc('refund_number')
            ->first();

        if ($lastRefund) {
            $lastSeq = (int) substr($lastRefund->refund_number, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    private function getDefaultVariantId($product): ?int
    {
        if (empty($product->variants)) {
            return null;
        }

        return $product->variants[0]->id;
    }
}
