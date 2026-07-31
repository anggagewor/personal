<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Application\DTO\DiscountData;
use Modules\Pos\Domain\Contracts\DiscountRepositoryInterface;
use Modules\Pos\Domain\Entities\Discount;
use Modules\Pos\Domain\Enums\DiscountType;
use Modules\Pos\Infrastructure\Models\DiscountModel;

class EloquentDiscountRepository implements DiscountRepositoryInterface
{
    public function findByOutlet(int $outletId): array
    {
        $models = DiscountModel::where('outlet_id', $outletId)
            ->orderBy('priority')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function findActiveByOutlet(int $outletId): array
    {
        $models = DiscountModel::where('outlet_id', $outletId)
            ->active()
            ->orderBy('priority')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    public function findById(int $id): ?Discount
    {
        $model = DiscountModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(int $outletId, DiscountData $data): Discount
    {
        $model = DiscountModel::create([
            'outlet_id' => $outletId,
            'name' => $data->name,
            'type' => $data->type,
            'value' => $data->value,
            'min_purchase' => $data->minPurchase,
            'member_only' => $data->memberOnly,
            'is_active' => $data->isActive,
            'priority' => $data->priority,
            'starts_at' => $data->startsAt,
            'ends_at' => $data->endsAt,
            'conditions' => $data->conditions,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, DiscountData $data): Discount
    {
        $model = DiscountModel::findOrFail($id);

        $model->update([
            'name' => $data->name,
            'type' => $data->type,
            'value' => $data->value,
            'min_purchase' => $data->minPurchase,
            'member_only' => $data->memberOnly,
            'is_active' => $data->isActive,
            'priority' => $data->priority,
            'starts_at' => $data->startsAt,
            'ends_at' => $data->endsAt,
            'conditions' => $data->conditions,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        DiscountModel::where('id', $id)->delete();
    }

    public function findApplicable(int $outletId, float $subtotal, ?int $memberId = null): array
    {
        $now = now();

        $query = DiscountModel::where('outlet_id', $outletId)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('min_purchase')
                    ->orWhere('min_purchase', '<=', $subtotal);
            });

        // Filter member_only: exclude member_only discounts when no member is provided
        if ($memberId === null) {
            $query->where('member_only', false);
        }

        $models = $query->orderBy('priority')->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    private function toEntity(DiscountModel $model): Discount
    {
        return new Discount(
            id: $model->id,
            outletId: $model->outlet_id,
            name: $model->name,
            type: DiscountType::from($model->type),
            value: (float) $model->value,
            minPurchase: $model->min_purchase ? (float) $model->min_purchase : null,
            buyQuantity: null,
            getQuantity: null,
            productId: null,
            startDate: $model->starts_at?->toDateTimeString(),
            endDate: $model->ends_at?->toDateTimeString(),
            isActive: (bool) $model->is_active,
            memberOnly: (bool) $model->member_only,
            priority: (int) $model->priority,
            conditions: $model->conditions,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
