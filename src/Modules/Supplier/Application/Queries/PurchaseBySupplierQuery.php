<?php

namespace Modules\Supplier\Application\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;

class PurchaseBySupplierQuery
{
    public function execute(int $outletId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = PurchaseOrderModel::where('supplier_purchase_orders.outlet_id', $outletId)
            ->where('supplier_purchase_orders.status', '!=', 'cancelled')
            ->join('supplier_suppliers', 'supplier_purchase_orders.supplier_id', '=', 'supplier_suppliers.id')
            ->select(
                'supplier_suppliers.id as supplier_id',
                'supplier_suppliers.name as supplier_name',
                DB::raw('SUM(supplier_purchase_orders.total_amount) as total_purchase'),
            )
            ->groupBy('supplier_suppliers.id', 'supplier_suppliers.name');

        if ($dateFrom) {
            $query->where('supplier_purchase_orders.order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('supplier_purchase_orders.order_date', '<=', $dateTo);
        }

        $results = $query->get();

        return $results->map(function ($row) {
            $totalPaid = (float) SupplierPaymentModel::join(
                'supplier_purchase_orders',
                'supplier_payments.purchase_order_id',
                '=',
                'supplier_purchase_orders.id'
            )
                ->where('supplier_purchase_orders.supplier_id', $row->supplier_id)
                ->where('supplier_purchase_orders.status', '!=', 'cancelled')
                ->sum('supplier_payments.amount');

            return [
                'supplier_id' => $row->supplier_id,
                'supplier_name' => $row->supplier_name,
                'total_purchase' => (float) $row->total_purchase,
                'outstanding_debt' => (float) $row->total_purchase - $totalPaid,
            ];
        })->all();
    }
}
