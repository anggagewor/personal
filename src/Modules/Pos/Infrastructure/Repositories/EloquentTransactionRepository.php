<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Application\DTO\CheckoutData;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Entities\Transaction;
use Modules\Pos\Domain\Entities\TransactionItem;
use Modules\Pos\Domain\Enums\TransactionStatus;
use Modules\Pos\Infrastructure\Models\TransactionItemModel;
use Modules\Pos\Infrastructure\Models\TransactionDiscountModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;

class EloquentTransactionRepository implements TransactionRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array
    {
        $query = TransactionModel::with('items')
            ->where('outlet_id', $outletId)
            ->orderByDesc('created_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method_type'])) {
            $query->where('payment_method_type', $filters['payment_method_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (!empty($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }

        $paginator = $query->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function findById(int $id): ?Transaction
    {
        $model = TransactionModel::with(['items', 'discounts'])->find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(int $outletId, CheckoutData $data): Transaction
    {
        return DB::transaction(function () use ($outletId, $data) {
            $subtotal = 0;
            foreach ($data->items as $item) {
                $subtotal += $item->quantity * $item->unitPrice;
            }

            $status = $data->status ?? TransactionStatus::Completed->value;
            $discountAmount = $data->discountAmount;
            $taxAmount = $data->taxAmount;
            $taxRate = $data->taxRate;
            $taxInclusive = $data->taxInclusive;

            // For tax exclusive: total = subtotal - discount + tax
            // For tax inclusive: total = subtotal - discount (tax is already inside)
            if ($taxInclusive) {
                $total = max(0, $subtotal - $discountAmount);
            } else {
                $total = max(0, $subtotal - $discountAmount + $taxAmount);
            }

            $changeAmount = null;
            if ($data->paymentMethodType === 'cash' && $data->amountTendered !== null) {
                $changeAmount = $data->amountTendered - $total;
            }

            $transactionNumber = $this->generateTransactionNumber($outletId);

            $model = TransactionModel::create([
                'outlet_id' => $outletId,
                'transaction_number' => $transactionNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'tax_inclusive' => $taxInclusive,
                'total' => $total,
                'payment_method' => $data->paymentMethod,
                'payment_method_type' => $data->paymentMethodType,
                'amount_tendered' => $data->amountTendered,
                'change_amount' => $changeAmount,
                'status' => $status,
                'member_id' => $data->memberId,
                'shift_id' => $data->shiftId,
                'voucher_code' => $data->voucherCode,
                'notes' => $data->notes,
            ]);

            foreach ($data->items as $item) {
                TransactionItemModel::create([
                    'transaction_id' => $model->id,
                    'product_id' => $item->productId,
                    'product_variant_id' => $item->productVariantId,
                    'product_name' => $item->productName,
                    'variant_name' => $item->variantName,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'subtotal' => $item->quantity * $item->unitPrice,
                ]);
            }

            // Save applied discounts detail
            foreach ($data->appliedDiscounts as $disc) {
                TransactionDiscountModel::create([
                    'transaction_id' => $model->id,
                    'discount_id' => $disc['discount_id'] ?? null,
                    'name' => $disc['name'],
                    'type' => $disc['type'],
                    'value' => $disc['value'],
                    'discount_amount' => $disc['amount'],
                ]);
            }

            return $this->toEntity($model->fresh()->load(['items', 'discounts']));
        });
    }

    public function void(int $id, string $reason): Transaction
    {
        $model = TransactionModel::findOrFail($id);

        $model->update([
            'status' => TransactionStatus::Voided->value,
            'void_reason' => $reason,
            'voided_at' => now(),
        ]);

        return $this->toEntity($model->fresh()->load('items'));
    }

    public function generateTransactionNumber(int $outletId): string
    {
        $today = Carbon::today();
        $prefix = 'TRX-' . $today->format('ymd') . '-';

        $lastTransaction = TransactionModel::where('outlet_id', $outletId)
            ->where('transaction_number', 'like', $prefix . '%')
            ->orderByDesc('transaction_number')
            ->first();

        if ($lastTransaction) {
            $lastSeq = (int) substr($lastTransaction->transaction_number, -4);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    public function findOpenBillsByOutlet(int $outletId): array
    {
        $models = TransactionModel::with('items')
            ->where('outlet_id', $outletId)
            ->where('status', TransactionStatus::Pending->value)
            ->orderBy('created_at')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function closeOpenBill(int $id, string $paymentMethod, string $paymentMethodType, ?float $amountTendered): Transaction
    {
        $model = TransactionModel::findOrFail($id);

        $changeAmount = null;
        if ($paymentMethodType === 'cash' && $amountTendered !== null) {
            $changeAmount = $amountTendered - (float) $model->total;
        }

        $model->update([
            'status' => TransactionStatus::Completed->value,
            'payment_method' => $paymentMethod,
            'payment_method_type' => $paymentMethodType,
            'amount_tendered' => $amountTendered,
            'change_amount' => $changeAmount,
            'updated_at' => now(),
        ]);

        return $this->toEntity($model->fresh()->load('items'));
    }

    public function createRefund(int $transactionId, string $refundNumber, float $refundAmount, string $reason, ?string $refundMethod, array $items): \Modules\Pos\Domain\Entities\Refund
    {
        return DB::transaction(function () use ($transactionId, $refundNumber, $refundAmount, $reason, $refundMethod, $items) {
            $refundModel = \Modules\Pos\Infrastructure\Models\RefundModel::create([
                'transaction_id' => $transactionId,
                'refund_number' => $refundNumber,
                'refund_amount' => $refundAmount,
                'reason' => $reason,
                'refund_method' => $refundMethod,
            ]);

            $refundItems = [];
            foreach ($items as $item) {
                $refundItemModel = \Modules\Pos\Infrastructure\Models\RefundItemModel::create([
                    'refund_id' => $refundModel->id,
                    'transaction_item_id' => $item['transaction_item_id'],
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'refund_amount' => $item['refund_amount'],
                    'created_at' => now(),
                ]);

                $refundItems[] = new \Modules\Pos\Domain\Entities\RefundItem(
                    id: $refundItemModel->id,
                    refundId: $refundModel->id,
                    transactionItemId: $item['transaction_item_id'],
                    productId: $item['product_id'],
                    productVariantId: $item['product_variant_id'] ?? null,
                    productName: $item['product_name'],
                    variantName: $item['variant_name'] ?? null,
                    quantity: $item['quantity'],
                    unitPrice: $item['unit_price'],
                    refundAmount: $item['refund_amount'],
                );
            }

            return new \Modules\Pos\Domain\Entities\Refund(
                id: $refundModel->id,
                transactionId: $transactionId,
                refundNumber: $refundNumber,
                refundAmount: $refundAmount,
                reason: $reason,
                refundMethod: $refundMethod,
                items: $refundItems,
                createdAt: new DateTimeImmutable($refundModel->created_at->toDateTimeString()),
            );
        });
    }

    public function updateRefundedAmount(int $transactionId, float $totalRefunded): void
    {
        TransactionModel::where('id', $transactionId)->update([
            'refunded_amount' => $totalRefunded,
        ]);
    }

    public function updateStatus(int $transactionId, string $status): void
    {
        TransactionModel::where('id', $transactionId)->update([
            'status' => $status,
        ]);
    }

    public function getRefundedQuantityByItem(int $transactionItemId): int
    {
        return (int) \Modules\Pos\Infrastructure\Models\RefundItemModel::where('transaction_item_id', $transactionItemId)
            ->sum('quantity');
    }

    public function findRefundsByTransaction(int $transactionId): array
    {
        $models = \Modules\Pos\Infrastructure\Models\RefundModel::with('items')
            ->where('transaction_id', $transactionId)
            ->orderByDesc('created_at')
            ->get();

        return $models->map(function ($model) {
            $items = $model->items->map(fn ($item) => new \Modules\Pos\Domain\Entities\RefundItem(
                id: $item->id,
                refundId: $model->id,
                transactionItemId: $item->transaction_item_id,
                productId: $item->product_id,
                productVariantId: $item->product_variant_id,
                productName: $item->product_name,
                variantName: $item->variant_name,
                quantity: (int) $item->quantity,
                unitPrice: (float) $item->unit_price,
                refundAmount: (float) $item->refund_amount,
            ))->all();

            return new \Modules\Pos\Domain\Entities\Refund(
                id: $model->id,
                transactionId: $model->transaction_id,
                refundNumber: $model->refund_number,
                refundAmount: (float) $model->refund_amount,
                reason: $model->reason,
                refundMethod: $model->refund_method,
                items: $items,
                createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            );
        })->all();
    }

    private function toEntity(TransactionModel $model): Transaction
    {
        $items = $model->items->map(fn ($itemModel) => new TransactionItem(
            id: $itemModel->id,
            transactionId: $model->id,
            productId: $itemModel->product_id,
            productVariantId: $itemModel->product_variant_id,
            productName: $itemModel->product_name,
            variantName: $itemModel->variant_name,
            quantity: (int) $itemModel->quantity,
            unitPrice: (float) $itemModel->unit_price,
            subtotal: (float) $itemModel->subtotal,
        ))->all();

        $appliedDiscounts = $model->relationLoaded('discounts')
            ? $model->discounts->map(fn ($d) => [
                'name' => $d->name,
                'type' => $d->type,
                'value' => (float) $d->value,
                'amount' => (float) $d->discount_amount,
            ])->all()
            : [];

        return new Transaction(
            id: $model->id,
            outletId: $model->outlet_id,
            transactionNumber: $model->transaction_number,
            subtotal: (float) $model->subtotal,
            discountAmount: (float) $model->discount_amount,
            taxRate: (float) ($model->tax_rate ?? 0),
            taxAmount: (float) ($model->tax_amount ?? 0),
            taxInclusive: (bool) ($model->tax_inclusive ?? false),
            total: (float) $model->total,
            refundedAmount: (float) ($model->refunded_amount ?? 0),
            paymentMethod: $model->payment_method,
            paymentMethodType: $model->payment_method_type,
            amountTendered: $model->amount_tendered ? (float) $model->amount_tendered : null,
            changeAmount: $model->change_amount ? (float) $model->change_amount : null,
            status: TransactionStatus::from($model->status),
            voidReason: $model->void_reason,
            memberId: $model->member_id,
            tableSessionId: $model->table_session_id,
            voucherCode: $model->voucher_code,
            items: $items,
            appliedDiscounts: $appliedDiscounts,
            notes: $model->notes,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            voidedAt: $model->voided_at ? new DateTimeImmutable($model->voided_at->toDateTimeString()) : null,
        );
    }
}
