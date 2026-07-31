<?php

namespace Modules\Pos\Application\Actions\Transaction;

use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;

class GenerateReceiptAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    /**
     * Generate receipt data for a transaction.
     *
     * Returns a structured array with all required receipt fields.
     * Actual rendering (thermal/PDF) is a presentation concern handled by the controller/resource layer.
     *
     * @return array{
     *     outlet_name: string,
     *     outlet_address: string|null,
     *     transaction_number: string,
     *     date_time: string,
     *     items: array<int, array{name: string, variant: string|null, quantity: int, unit_price: float, subtotal: float}>,
     *     discount_amount: float,
     *     subtotal: float,
     *     total: float,
     *     payment_method: string|null,
     *     amount_tendered: float|null,
     *     change: float|null,
     *     voucher_code: string|null,
     *     member_id: int|null,
     * }
     */
    public function execute(int $transactionId): array
    {
        $transaction = $this->transactionRepo->findById($transactionId);

        if ($transaction === null) {
            throw new \RuntimeException("Transaksi tidak ditemukan.");
        }

        $outlet = $this->outletRepo->findById($transaction->outletId);

        $items = array_map(fn ($item) => [
            'name' => $item->productName,
            'variant' => $item->variantName,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice,
            'subtotal' => $item->subtotal,
        ], $transaction->items);

        return [
            'outlet_name' => $outlet?->name ?? '',
            'outlet_address' => $outlet?->address,
            'transaction_number' => $transaction->transactionNumber,
            'date_time' => $transaction->createdAt?->format('Y-m-d H:i:s') ?? '',
            'items' => $items,
            'discount_amount' => $transaction->discountAmount,
            'subtotal' => $transaction->subtotal,
            'total' => $transaction->total,
            'payment_method' => $transaction->paymentMethod,
            'amount_tendered' => $transaction->amountTendered,
            'change' => $transaction->changeAmount,
            'voucher_code' => $transaction->voucherCode,
            'member_id' => $transaction->memberId,
        ];
    }
}
