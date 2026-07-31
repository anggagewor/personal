<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Application\DTO\VoucherData;
use Modules\Pos\Domain\Contracts\VoucherRepositoryInterface;
use Modules\Pos\Domain\Entities\Voucher;
use Modules\Pos\Domain\Enums\DiscountType;
use Modules\Pos\Infrastructure\Models\VoucherModel;
use Modules\Pos\Infrastructure\Models\VoucherRedemptionModel;

class EloquentVoucherRepository implements VoucherRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, int $perPage): array
    {
        $paginator = VoucherModel::where('outlet_id', $outletId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

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

    public function findByCode(string $code): ?Voucher
    {
        $model = VoucherModel::where('code', $code)->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findById(int $id): ?Voucher
    {
        $model = VoucherModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(int $outletId, VoucherData $data): Voucher
    {
        $model = VoucherModel::create([
            'outlet_id' => $outletId,
            'code' => $data->code,
            'type' => $data->type,
            'value' => $data->value,
            'min_purchase' => $data->minPurchase,
            'usage_limit' => $data->usageLimit,
            'usage_count' => 0,
            'expires_at' => $data->expiresAt,
            'is_active' => $data->isActive,
        ]);

        return $this->toEntity($model);
    }

    public function batchCreate(int $outletId, array $vouchers): array
    {
        $created = [];

        foreach ($vouchers as $voucherData) {
            $model = VoucherModel::create([
                'outlet_id' => $outletId,
                'code' => $voucherData->code,
                'type' => $voucherData->type,
                'value' => $voucherData->value,
                'min_purchase' => $voucherData->minPurchase,
                'usage_limit' => $voucherData->usageLimit,
                'usage_count' => 0,
                'expires_at' => $voucherData->expiresAt,
                'is_active' => $voucherData->isActive,
            ]);

            $created[] = $this->toEntity($model);
        }

        return $created;
    }

    public function incrementUsage(int $id): void
    {
        VoucherModel::where('id', $id)->increment('usage_count');
    }

    public function recordRedemption(int $voucherId, int $transactionId): void
    {
        VoucherRedemptionModel::create([
            'voucher_id' => $voucherId,
            'transaction_id' => $transactionId,
            'discount_amount' => 0,
            'redeemed_at' => now(),
        ]);
    }

    private function toEntity(VoucherModel $model): Voucher
    {
        return new Voucher(
            id: $model->id,
            outletId: $model->outlet_id,
            code: $model->code,
            type: DiscountType::from($model->type),
            value: (float) $model->value,
            minPurchase: $model->min_purchase ? (float) $model->min_purchase : null,
            usageLimit: $model->usage_limit,
            usageCount: (int) $model->usage_count,
            expiresAt: $model->expires_at ? new DateTimeImmutable($model->expires_at->toDateTimeString()) : null,
            isActive: (bool) $model->is_active,
            productId: $model->product_id ? (int) $model->product_id : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
