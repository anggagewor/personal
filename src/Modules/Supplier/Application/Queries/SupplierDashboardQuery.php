<?php

namespace Modules\Supplier\Application\Queries;

use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;

class SupplierDashboardQuery
{
    public function execute(int $outletId): array
    {
        // Total outstanding debt (all non-cancelled POs)
        $totalPurchase = (float) PurchaseOrderModel::where('outlet_id', $outletId)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        $totalPaid = (float) SupplierPaymentModel::join(
            'supplier_purchase_orders',
            'supplier_payments.purchase_order_id',
            '=',
            'supplier_purchase_orders.id'
        )
            ->where('supplier_purchase_orders.outlet_id', $outletId)
            ->where('supplier_purchase_orders.status', '!=', 'cancelled')
            ->sum('supplier_payments.amount');

        // Pending PO count (confirmed but not fully received)
        $pendingCount = PurchaseOrderModel::where('outlet_id', $outletId)
            ->whereIn('status', ['confirmed', 'partial'])
            ->count();

        // Recent POs (last 5)
        $recentPOs = PurchaseOrderModel::with('items')
            ->where('outlet_id', $outletId)
            ->orderByDesc('order_date')
            ->limit(5)
            ->get();

        return [
            'total_outstanding_debt' => $totalPurchase - $totalPaid,
            'pending_po_count' => $pendingCount,
            'recent_purchase_orders' => $recentPOs,
        ];
    }
}
