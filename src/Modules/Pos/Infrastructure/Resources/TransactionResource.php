<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\Transaction;
use Modules\Pos\Infrastructure\Models\TransactionModel;

class TransactionResource
{
    /**
     * @param Transaction|TransactionModel $transaction
     */
    public static function toArray(Transaction|TransactionModel $transaction): array
    {
        if ($transaction instanceof TransactionModel) {
            return self::fromModel($transaction);
        }

        return self::fromEntity($transaction);
    }

    private static function fromEntity(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'outlet_id' => $transaction->outletId,
            'transaction_number' => $transaction->transactionNumber,
            'subtotal' => $transaction->subtotal,
            'discount_amount' => $transaction->discountAmount,
            'tax_rate' => $transaction->taxRate,
            'tax_amount' => $transaction->taxAmount,
            'tax_inclusive' => $transaction->taxInclusive,
            'total' => $transaction->total,
            'refunded_amount' => $transaction->refundedAmount,
            'payment_method' => $transaction->paymentMethod,
            'payment_method_type' => $transaction->paymentMethodType,
            'amount_tendered' => $transaction->amountTendered,
            'change_amount' => $transaction->changeAmount,
            'status' => $transaction->status->value,
            'void_reason' => $transaction->voidReason,
            'voided_at' => $transaction->voidedAt?->format('Y-m-d\TH:i:s.000000\Z'),
            'member_id' => $transaction->memberId,
            'table_session_id' => $transaction->tableSessionId,
            'voucher_code' => $transaction->voucherCode,
            'notes' => $transaction->notes,
            'items' => TransactionItemResource::collection($transaction->items),
            'applied_discounts' => $transaction->appliedDiscounts,
            'created_at' => $transaction->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    private static function fromModel(TransactionModel $model): array
    {
        return [
            'id' => $model->id,
            'outlet_id' => $model->outlet_id,
            'transaction_number' => $model->transaction_number,
            'subtotal' => (float) $model->subtotal,
            'discount_amount' => (float) $model->discount_amount,
            'tax_rate' => (float) ($model->tax_rate ?? 0),
            'tax_amount' => (float) ($model->tax_amount ?? 0),
            'tax_inclusive' => (bool) ($model->tax_inclusive ?? false),
            'total' => (float) $model->total,
            'refunded_amount' => (float) ($model->refunded_amount ?? 0),
            'payment_method' => $model->payment_method,
            'payment_method_type' => $model->payment_method_type,
            'amount_tendered' => $model->amount_tendered ? (float) $model->amount_tendered : null,
            'change_amount' => $model->change_amount ? (float) $model->change_amount : null,
            'status' => $model->status,
            'void_reason' => $model->void_reason,
            'voided_at' => $model->voided_at?->format('Y-m-d\TH:i:s.000000\Z'),
            'member_id' => $model->member_id,
            'table_session_id' => $model->table_session_id,
            'voucher_code' => $model->voucher_code,
            'notes' => $model->notes,
            'items' => $model->relationLoaded('items')
                ? $model->items->map(fn ($item) => [
                    'id' => $item->id,
                    'transaction_id' => $item->transaction_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ])->all()
                : [],
            'created_at' => $model->created_at?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $transactions): array
    {
        return array_map(fn ($transaction) => self::toArray($transaction), $transactions);
    }
}
