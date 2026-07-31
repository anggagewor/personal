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
        $model = TransactionModel::with('items')->find($id);

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
            $total = $subtotal; // Discount calculation handled by the action layer

            $changeAmount = null;
            if ($data->paymentMethodType === 'cash' && $data->amountTendered !== null) {
                $changeAmount = $data->amountTendered - $total;
            }

            $transactionNumber = $this->generateTransactionNumber($outletId);

            $model = TransactionModel::create([
                'outlet_id' => $outletId,
                'transaction_number' => $transactionNumber,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total' => $total,
                'payment_method' => $data->paymentMethod,
                'payment_method_type' => $data->paymentMethodType,
                'amount_tendered' => $data->amountTendered,
                'change_amount' => $changeAmount,
                'status' => $status,
                'member_id' => $data->memberId,
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

            return $this->toEntity($model->fresh()->load('items'));
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

        return new Transaction(
            id: $model->id,
            outletId: $model->outlet_id,
            transactionNumber: $model->transaction_number,
            subtotal: (float) $model->subtotal,
            discountAmount: (float) $model->discount_amount,
            total: (float) $model->total,
            paymentMethod: $model->payment_method,
            amountTendered: $model->amount_tendered ? (float) $model->amount_tendered : null,
            changeAmount: $model->change_amount ? (float) $model->change_amount : null,
            status: TransactionStatus::from($model->status),
            voidReason: $model->void_reason,
            memberId: $model->member_id,
            tableSessionId: $model->table_session_id,
            voucherCode: $model->voucher_code,
            items: $items,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            voidedAt: $model->voided_at ? new DateTimeImmutable($model->voided_at->toDateTimeString()) : null,
        );
    }
}
