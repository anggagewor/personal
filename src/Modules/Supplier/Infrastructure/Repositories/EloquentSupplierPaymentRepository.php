<?php

namespace Modules\Supplier\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Supplier\Application\DTO\SupplierPaymentData;
use Modules\Supplier\Domain\Contracts\SupplierPaymentRepositoryInterface;
use Modules\Supplier\Domain\Entities\SupplierPayment;
use Modules\Supplier\Domain\Enums\PaymentMethod;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;

class EloquentSupplierPaymentRepository implements SupplierPaymentRepositoryInterface
{
    /**
     * @return SupplierPayment[]
     */
    public function findByPurchaseOrder(int $purchaseOrderId): array
    {
        $models = SupplierPaymentModel::where('purchase_order_id', $purchaseOrderId)
            ->orderByDesc('payment_date')
            ->get();

        return $models->map(fn ($model) => $this->toEntity($model))->all();
    }

    /**
     * @return array{data: SupplierPayment[], total: int, per_page: int, current_page: int}
     */
    public function findBySupplierPaginated(int $supplierId, int $perPage): array
    {
        $paginator = SupplierPaymentModel::query()
            ->join('supplier_purchase_orders', 'supplier_payments.purchase_order_id', '=', 'supplier_purchase_orders.id')
            ->where('supplier_purchase_orders.supplier_id', $supplierId)
            ->select('supplier_payments.*')
            ->orderByDesc('supplier_payments.payment_date')
            ->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }

    public function create(SupplierPaymentData $data): SupplierPayment
    {
        $model = SupplierPaymentModel::create([
            'purchase_order_id' => $data->purchaseOrderId,
            'amount' => $data->amount,
            'payment_date' => $data->paymentDate,
            'payment_method' => $data->paymentMethod->value,
            'notes' => $data->notes,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function getTotalPaidForPO(int $purchaseOrderId): float
    {
        return (float) SupplierPaymentModel::where('purchase_order_id', $purchaseOrderId)
            ->sum('amount');
    }

    private function toEntity(SupplierPaymentModel $model): SupplierPayment
    {
        return new SupplierPayment(
            id: $model->id,
            purchaseOrderId: $model->purchase_order_id,
            amount: (float) $model->amount,
            paymentDate: $model->payment_date->format('Y-m-d'),
            paymentMethod: PaymentMethod::from($model->payment_method),
            notes: $model->notes,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
        );
    }
}
