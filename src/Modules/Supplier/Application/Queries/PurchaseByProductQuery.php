<?php

namespace Modules\Supplier\Application\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderItemModel;

class PurchaseByProductQuery
{
    public function execute(int $outletId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = PurchaseOrderItemModel::join(
            'supplier_purchase_orders',
            'supplier_purchase_order_items.purchase_order_id',
            '=',
            'supplier_purchase_orders.id'
        )
            ->where('supplier_purchase_orders.outlet_id', $outletId)
            ->where('supplier_purchase_orders.status', '!=', 'cancelled')
            ->select(
                'supplier_purchase_order_items.product_variant_id',
                'supplier_purchase_order_items.product_name',
                'supplier_purchase_order_items.variant_name',
                DB::raw('SUM(supplier_purchase_order_items.quantity) as total_quantity'),
                DB::raw('SUM(supplier_purchase_order_items.subtotal) as total_cost'),
            )
            ->groupBy(
                'supplier_purchase_order_items.product_variant_id',
                'supplier_purchase_order_items.product_name',
                'supplier_purchase_order_items.variant_name',
            );

        if ($dateFrom) {
            $query->where('supplier_purchase_orders.order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('supplier_purchase_orders.order_date', '<=', $dateTo);
        }

        return $query->get()->map(fn ($row) => [
            'product_variant_id' => $row->product_variant_id,
            'product_name' => $row->product_name,
            'variant_name' => $row->variant_name,
            'total_quantity' => (int) $row->total_quantity,
            'total_cost' => (float) $row->total_cost,
        ])->all();
    }
}
