<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Outlet;
use Modules\Pos\Domain\Entities\Transaction;

class ReceiptResource
{
    /**
     * Build receipt data from a transaction and its outlet.
     */
    public static function toArray(Transaction $transaction, Outlet $outlet): array
    {
        return [
            'outlet_name' => $outlet->name,
            'outlet_address' => $outlet->address,
            'transaction_number' => $transaction->transactionNumber,
            'date_time' => $transaction->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'items' => TransactionItemResource::collection($transaction->items),
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
