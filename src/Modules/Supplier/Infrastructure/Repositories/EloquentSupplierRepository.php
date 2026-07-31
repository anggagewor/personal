<?php

namespace Modules\Supplier\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Supplier\Application\DTO\SupplierData;
use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Supplier\Domain\Entities\Supplier;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierModel;

class EloquentSupplierRepository implements SupplierRepositoryInterface
{
    /**
     * @param array{search?: string, status?: string} $filters
     * @return array{data: Supplier[], total: int, per_page: int, current_page: int}
     */
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array
    {
        $query = SupplierModel::where('outlet_id', $outletId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage);

        return [
            'data' => collect($paginator->items())
                ->map(fn (SupplierModel $model) => $this->toEntity($model))
                ->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }

    public function findById(int $id): ?Supplier
    {
        $model = SupplierModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(int $outletId, SupplierData $data): Supplier
    {
        $model = SupplierModel::create([
            'outlet_id' => $outletId,
            'name' => $data->name,
            'address' => $data->address,
            'phone' => $data->phone,
            'email' => $data->email,
            'bank_name' => $data->bankName,
            'bank_account_number' => $data->bankAccountNumber,
            'bank_account_holder' => $data->bankAccountHolder,
            'notes' => $data->notes,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, SupplierData $data): Supplier
    {
        $model = SupplierModel::findOrFail($id);

        $model->update([
            'name' => $data->name,
            'address' => $data->address,
            'phone' => $data->phone,
            'email' => $data->email,
            'bank_name' => $data->bankName,
            'bank_account_number' => $data->bankAccountNumber,
            'bank_account_holder' => $data->bankAccountHolder,
            'notes' => $data->notes,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function softDelete(int $id): void
    {
        $model = SupplierModel::findOrFail($id);
        $model->delete();
    }

    /**
     * @return Supplier[]
     */
    public function search(int $outletId, string $query): array
    {
        return SupplierModel::where('outlet_id', $outletId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (SupplierModel $model) => $this->toEntity($model))
            ->all();
    }

    public function existsByName(int $outletId, string $name, ?int $excludeId = null): bool
    {
        $query = SupplierModel::where('outlet_id', $outletId)
            ->where('name', $name);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getTotalDebt(int $supplierId): float
    {
        $totalAmount = (float) PurchaseOrderModel::where('supplier_id', $supplierId)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $totalPaid = (float) PurchaseOrderModel::where('supplier_id', $supplierId)
            ->where('status', '!=', 'cancelled')
            ->join('supplier_payments', 'supplier_purchase_orders.id', '=', 'supplier_payments.purchase_order_id')
            ->sum('supplier_payments.amount');

        return $totalAmount - $totalPaid;
    }

    private function toEntity(SupplierModel $model): Supplier
    {
        return new Supplier(
            id: $model->id,
            outletId: $model->outlet_id,
            name: $model->name,
            address: $model->address,
            phone: $model->phone,
            email: $model->email,
            bankName: $model->bank_name,
            bankAccountNumber: $model->bank_account_number,
            bankAccountHolder: $model->bank_account_holder,
            notes: $model->notes,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
            updatedAt: $model->updated_at
                ? new DateTimeImmutable($model->updated_at->toDateTimeString())
                : null,
            deletedAt: $model->deleted_at
                ? new DateTimeImmutable($model->deleted_at->toDateTimeString())
                : null,
        );
    }
}
