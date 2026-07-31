<?php

namespace Modules\Supplier\Application\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Supplier\Infrastructure\Models\PurchaseOrderModel;
use Modules\Supplier\Infrastructure\Models\SupplierPaymentModel;

class PurchaseSummaryQuery
{
    public function execute(int $outletId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = PurchaseOrderModel::where('outlet_id', $outletId)
            ->where('status', '!=', 'cancelled');

        if ($dateFrom) {
            $query->where('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('order_date', '<=', $dateTo);
        }

        $totalPurchase = (float) $query->sum('total_amount');
        $purchaseCount = $query->count();

        // Total paid from payments of non-cancelled POs
        $poIds = $query->pluck('id');
        $totalPaid = (float) SupplierPaymentModel::whereIn('purchase_order_id', $poIds)->sum('amount');

        return [
            'total_purchase_value' => $totalPurchase,
            'total_paid' => $totalPaid,
            'total_outstanding_debt' => $totalPurchase - $totalPaid,
            'purchase_count' => $purchaseCount,
        ];
    }
}
