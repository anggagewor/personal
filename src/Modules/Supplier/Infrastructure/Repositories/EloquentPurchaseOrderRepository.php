<?php

namespace Modules\Supplier\Infrastructure\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Application\DTO\PurchaseOrderData;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Entities\PurchaseOrder;
use Modules\Supplier\Domain\Entities\PurchaseOrderItem;
use Modules\Supplier\Domain\Enums\PaymentStatus;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;

class EloquentPurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array
    {
        $query = PurchaseOrderModel::with('items')
            ->where('outlet_id', $outletId)
            ->orderByDesc('order_date');

        $this->applyFilters($query, $filters);

        $paginator = $query->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }

    public function findById(int $id): ?PurchaseOrder
    {
        $model = PurchaseOrderModel::with('items')->find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function create(int $outletId, PurchaseOrderData $data): PurchaseOrder
    {
        return DB::transaction(function () use ($outletId, $data) {
            $poNumber = $this->generatePoNumber($outletId);

            $totalAmount = collect($data->items)->sum(
                fn ($item) => $item->quantity * $item->unitCost
            );

            $model = PurchaseOrderModel::create([
                'outlet_id' => $outletId,
                'supplier_id' => $data->supplierId,
                'po_number' => $poNumber,
                'order_date' => $data->orderDate,
                'expected_delivery_date' => $data->expectedDeliveryDate,
                'status' => PurchaseOrderStatus::Draft->value,
                'payment_status' => PaymentStatus::Unpaid->value,
                'total_amount' => $totalAmount,
                'notes' => $data->notes,
            ]);

            foreach ($data->items as $item) {
                PurchaseOrderItemModel::create([
                    'purchase_order_id' => $model->id,
                    'product_variant_id' => $item->productVariantId,
                    'product_name' => $item->productName,
                    'variant_name' => $item->variantName,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unitCost,
                    'subtotal' => $item->quantity * $item->unitCost,
                    'received_quantity' => 0,
                ]);
            }

            return $this->toEntity($model->fresh()->load('items'));
        });
    }

    public function update(int $id, PurchaseOrderData $data): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $model = PurchaseOrderModel::findOrFail($id);

            $totalAmount = collect($data->items)->sum(
                fn ($item) => $item->quantity * $item->unitCost
            );

            $model->update([
                'supplier_id' => $data->supplierId,
                'order_date' => $data->orderDate,
                'expected_delivery_date' => $data->expectedDeliveryDate,
                'total_amount' => $totalAmount,
                'notes' => $data->notes,
            ]);

            // Sync items: delete old, create new
            $model->items()->delete();

            foreach ($data->items as $item) {
                PurchaseOrderItemModel::create([
                    'purchase_order_id' => $model->id,
                    'product_variant_id' => $item->productVariantId,
                    'product_name' => $item->productName,
                    'variant_name' => $item->variantName,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unitCost,
                    'subtotal' => $item->quantity * $item->unitCost,
                    'received_quantity' => 0,
                ]);
            }

            return $this->toEntity($model->fresh()->load('items'));
        });
    }

    public function updateStatus(int $id, PurchaseOrderStatus $status): void
    {
        $model = PurchaseOrderModel::findOrFail($id);

        $data = ['status' => $status->value];

        if ($status === PurchaseOrderStatus::Cancelled) {
            $data['cancelled_at'] = now();
        }

        $model->update($data);
    }

    public function updatePaymentStatus(int $id, PaymentStatus $status): void
    {
        PurchaseOrderModel::where('id', $id)->update([
            'payment_status' => $status->value,
        ]);
    }

    public function generatePoNumber(int $outletId): string
    {
        $today = Carbon::today();
        $datePrefix = 'PO-' . $today->format('Ymd') . '-';

        $lastPo = PurchaseOrderModel::where('outlet_id', $outletId)
            ->where('po_number', 'like', $datePrefix . '%')
            ->orderByDesc('po_number')
            ->first();

        if ($lastPo) {
            $lastSeq = (int) substr($lastPo->po_number, -3);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $datePrefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    public function findBySupplier(int $supplierId, array $filters, int $perPage): array
    {
        $query = PurchaseOrderModel::with('items')
            ->where('supplier_id', $supplierId)
            ->orderByDesc('order_date');

        $this->applyFilters($query, $filters);

        $paginator = $query->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ];
    }

    public function getOutstandingBySupplier(int $supplierId): float
    {
        $totalAmount = PurchaseOrderModel::where('supplier_id', $supplierId)
            ->where('status', '!=', PurchaseOrderStatus::Cancelled->value)
            ->sum('total_amount');

        $totalPaid = DB::table('supplier_payments')
            ->join('supplier_purchase_orders', 'supplier_payments.purchase_order_id', '=', 'supplier_purchase_orders.id')
            ->where('supplier_purchase_orders.supplier_id', $supplierId)
            ->where('supplier_purchase_orders.status', '!=', PurchaseOrderStatus::Cancelled->value)
            ->sum('supplier_payments.amount');

        return (float) $totalAmount - (float) $totalPaid;
    }

    public function getTotalPaid(int $purchaseOrderId): float
    {
        return (float) DB::table('supplier_payments')
            ->where('purchase_order_id', $purchaseOrderId)
            ->sum('amount');
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%");
            });
        }
    }

    private function toEntity(PurchaseOrderModel $model): PurchaseOrder
    {
        $items = $model->items->map(fn ($itemModel) => new PurchaseOrderItem(
            id: $itemModel->id,
            purchaseOrderId: $model->id,
            productVariantId: (int) $itemModel->product_variant_id,
            productName: $itemModel->product_name,
            variantName: $itemModel->variant_name,
            quantity: (int) $itemModel->quantity,
            unitCost: (float) $itemModel->unit_cost,
            subtotal: (float) $itemModel->subtotal,
            receivedQuantity: (int) $itemModel->received_quantity,
        ))->all();

        return new PurchaseOrder(
            id: $model->id,
            outletId: (int) $model->outlet_id,
            supplierId: (int) $model->supplier_id,
            poNumber: $model->po_number,
            orderDate: $model->order_date?->format('Y-m-d') ?? '',
            expectedDeliveryDate: $model->expected_delivery_date?->format('Y-m-d'),
            status: PurchaseOrderStatus::from($model->status),
            paymentStatus: PaymentStatus::from($model->payment_status),
            totalAmount: (float) $model->total_amount,
            notes: $model->notes,
            cancelledAt: $model->cancelled_at ? new DateTimeImmutable($model->cancelled_at->toDateTimeString()) : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
            items: $items,
        );
    }
}
