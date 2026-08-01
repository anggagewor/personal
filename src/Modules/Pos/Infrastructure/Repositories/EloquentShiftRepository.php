<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Domain\Contracts\ShiftRepositoryInterface;
use Modules\Pos\Domain\Entities\CashierShift;
use Modules\Pos\Domain\Enums\ShiftStatus;
use Modules\Pos\Infrastructure\Models\CashierShiftModel;
use Modules\Pos\Infrastructure\Models\TransactionModel;

class EloquentShiftRepository implements ShiftRepositoryInterface
{
    public function findById(int $id): ?CashierShift
    {
        $model = CashierShiftModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findActiveByOutletAndUser(int $outletId, int $userId): ?CashierShift
    {
        $model = CashierShiftModel::where('outlet_id', $outletId)
            ->where('user_id', $userId)
            ->where('status', ShiftStatus::Open->value)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findActiveByOutlet(int $outletId): ?CashierShift
    {
        $model = CashierShiftModel::where('outlet_id', $outletId)
            ->where('status', ShiftStatus::Open->value)
            ->latest('opened_at')
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array
    {
        $query = CashierShiftModel::where('outlet_id', $outletId)
            ->orderByDesc('opened_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('opened_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('opened_at', '<=', $filters['date_to'] . ' 23:59:59');
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

    public function create(int $outletId, int $userId, string $cashierName, float $openingAmount): CashierShift
    {
        $model = CashierShiftModel::create([
            'outlet_id' => $outletId,
            'user_id' => $userId,
            'cashier_name' => $cashierName,
            'opening_amount' => $openingAmount,
            'status' => ShiftStatus::Open->value,
            'opened_at' => now(),
        ]);

        return $this->toEntity($model);
    }

    public function close(int $id, float $closingAmount, float $expectedAmount, ?string $notes): CashierShift
    {
        $model = CashierShiftModel::findOrFail($id);

        $difference = $closingAmount - $expectedAmount;

        $model->update([
            'closing_amount' => $closingAmount,
            'expected_amount' => $expectedAmount,
            'difference' => $difference,
            'status' => ShiftStatus::Closed->value,
            'notes' => $notes,
            'closed_at' => now(),
        ]);

        return $this->toEntity($model->fresh());
    }

    public function getCashSalesDuringShift(int $shiftId): float
    {
        return (float) TransactionModel::where('shift_id', $shiftId)
            ->where('payment_method_type', 'cash')
            ->whereIn('status', ['completed', 'partially_refunded'])
            ->sum('total');
    }

    public function getCashRefundsDuringShift(int $shiftId): float
    {
        // Sum refund amounts for cash refunds on transactions in this shift
        return (float) \Modules\Pos\Infrastructure\Models\RefundModel::whereHas('transaction', function ($q) use ($shiftId) {
            $q->where('shift_id', $shiftId);
        })->where('refund_method', 'cash')->sum('refund_amount');
    }

    private function toEntity(CashierShiftModel $model): CashierShift
    {
        return new CashierShift(
            id: $model->id,
            outletId: $model->outlet_id,
            userId: $model->user_id,
            cashierName: $model->cashier_name,
            openingAmount: (float) $model->opening_amount,
            closingAmount: $model->closing_amount !== null ? (float) $model->closing_amount : null,
            expectedAmount: $model->expected_amount !== null ? (float) $model->expected_amount : null,
            difference: $model->difference !== null ? (float) $model->difference : null,
            status: ShiftStatus::from($model->status),
            notes: $model->notes,
            openedAt: $model->opened_at ? new DateTimeImmutable($model->opened_at->toDateTimeString()) : null,
            closedAt: $model->closed_at ? new DateTimeImmutable($model->closed_at->toDateTimeString()) : null,
        );
    }
}
