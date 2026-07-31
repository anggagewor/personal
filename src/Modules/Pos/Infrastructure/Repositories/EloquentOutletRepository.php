<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Application\DTO\OutletData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Entities\Outlet;
use Modules\Pos\Domain\Enums\BusinessType;
use Modules\Pos\Domain\Enums\PaymentFlowMode;
use Modules\Pos\Infrastructure\Models\OutletModel;

class EloquentOutletRepository implements OutletRepositoryInterface
{
    public function findById(int $id): ?Outlet
    {
        $model = OutletModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUser(int $userId): array
    {
        return OutletModel::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (OutletModel $model) => $this->toEntity($model))
            ->all();
    }

    public function create(int $userId, OutletData $data): Outlet
    {
        $model = OutletModel::create([
            'user_id' => $userId,
            'name' => $data->name,
            'business_type' => $data->businessType->value,
            'payment_flow' => $data->paymentFlow->value,
            'address' => $data->address,
            'phone' => $data->phone,
            'settings' => $data->settings,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function update(int $id, OutletData $data): Outlet
    {
        $model = OutletModel::findOrFail($id);

        $model->update([
            'name' => $data->name,
            'business_type' => $data->businessType->value,
            'payment_flow' => $data->paymentFlow->value,
            'address' => $data->address,
            'phone' => $data->phone,
            'settings' => $data->settings,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function softDelete(int $id): void
    {
        OutletModel::where('id', $id)->delete();
    }

    private function toEntity(OutletModel $model): Outlet
    {
        return new Outlet(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            businessType: BusinessType::from($model->business_type),
            paymentFlow: PaymentFlowMode::from($model->payment_flow),
            address: $model->address,
            phone: $model->phone,
            settings: $model->settings,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
            deletedAt: $model->deleted_at
                ? new DateTimeImmutable($model->deleted_at->toDateTimeString())
                : null,
        );
    }
}
