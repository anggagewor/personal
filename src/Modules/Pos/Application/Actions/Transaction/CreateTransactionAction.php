<?php

namespace Modules\Pos\Application\Actions\Transaction;

use Modules\Pos\Application\DTO\CheckoutData;
use Modules\Pos\Domain\Contracts\MemberRepositoryInterface;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Entities\Transaction;
use Modules\Pos\Domain\Enums\PaymentFlowMode;
use Modules\Pos\Domain\Enums\TransactionStatus;
use Modules\Pos\Domain\Exceptions\InsufficientStockException;

class CreateTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private ProductRepositoryInterface $productRepo,
        private OutletRepositoryInterface $outletRepo,
        private MemberRepositoryInterface $memberRepo,
    ) {}

    public function execute(CheckoutData $data): Transaction
    {
        $outlet = $this->outletRepo->findById($data->outletId);

        // Validate stock for each line item
        foreach ($data->items as $item) {
            $product = $this->productRepo->findById($item->productId);

            if ($product && $product->trackStock) {
                $variantId = $item->productVariantId ?? $this->getDefaultVariantId($product);

                if ($variantId) {
                    $stockLevel = $this->productRepo->getStockLevel($variantId);

                    if ($stockLevel < $item->quantity) {
                        throw InsufficientStockException::create(
                            $item->productName ?: $product->name,
                            $stockLevel,
                            $item->quantity,
                        );
                    }
                }
            }
        }

        // Link member with fallback to walk-in (Requirement 13.8)
        $memberId = $this->resolveMemberId($data->memberId);

        // Determine transaction status based on payment flow
        $status = $this->resolveStatus($data, $outlet);

        // Build checkout data with resolved member and status
        $checkoutData = new CheckoutData(
            outletId: $data->outletId,
            items: $data->items,
            paymentMethod: $data->paymentMethod,
            paymentMethodType: $data->paymentMethodType,
            amountTendered: $data->amountTendered,
            memberId: $memberId,
            voucherCode: $data->voucherCode,
            notes: $data->notes,
            status: $status,
        );

        // Create transaction via repository (handles transaction number generation)
        $transaction = $this->transactionRepo->create($data->outletId, $checkoutData);

        // Decrement stock for items with stock tracking enabled
        foreach ($data->items as $item) {
            $product = $this->productRepo->findById($item->productId);

            if ($product && $product->trackStock) {
                $variantId = $item->productVariantId ?? $this->getDefaultVariantId($product);

                if ($variantId) {
                    $this->productRepo->decrementStock($variantId, $item->quantity);
                }
            }
        }

        return $transaction;
    }

    private function resolveMemberId(?int $memberId): ?int
    {
        if ($memberId === null) {
            return null;
        }

        try {
            $member = $this->memberRepo->findById($memberId);

            return $member?->id;
        } catch (\Throwable) {
            // Fallback to walk-in on any failure
            return null;
        }
    }

    private function resolveStatus(CheckoutData $data, $outlet): string
    {
        // If status is explicitly provided (e.g., from QR order), use it
        if ($data->status !== null) {
            return $data->status;
        }

        if ($outlet === null) {
            return TransactionStatus::Completed->value;
        }

        // Pay-later flow creates an Open Bill (pending)
        if ($outlet->paymentFlow === PaymentFlowMode::PayLater) {
            return TransactionStatus::Pending->value;
        }

        // Pay-first requires immediate payment (completed)
        if ($outlet->paymentFlow === PaymentFlowMode::PayFirst) {
            return TransactionStatus::Completed->value;
        }

        // "Both" mode: determine by presence of payment data
        if ($outlet->paymentFlow === PaymentFlowMode::Both) {
            if ($data->paymentMethod === null) {
                return TransactionStatus::Pending->value;
            }

            return TransactionStatus::Completed->value;
        }

        return TransactionStatus::Completed->value;
    }

    private function getDefaultVariantId($product): ?int
    {
        if (empty($product->variants)) {
            return null;
        }

        return $product->variants[0]->id;
    }
}
