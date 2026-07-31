<?php

namespace Modules\Supplier\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Supplier\Application\Queries\PurchaseByProductQuery;
use Modules\Supplier\Application\Queries\PurchaseBySupplierQuery;
use Modules\Supplier\Application\Queries\PurchaseSummaryQuery;
use Modules\Supplier\Application\Queries\SupplierDashboardQuery;
use Modules\Supplier\Infrastructure\Requests\ExportReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierReportController extends Controller
{
    public function __construct(
        private PurchaseSummaryQuery $purchaseSummaryQuery,
        private PurchaseBySupplierQuery $purchaseBySupplierQuery,
        private PurchaseByProductQuery $purchaseByProductQuery,
        private SupplierDashboardQuery $supplierDashboardQuery,
    ) {}

    public function summary(Request $request, int $outletId): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $result = $this->purchaseSummaryQuery->execute($outletId, $dateFrom, $dateTo);

        return response()->json(['data' => $result]);
    }

    public function bySupplier(Request $request, int $outletId): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $result = $this->purchaseBySupplierQuery->execute($outletId, $dateFrom, $dateTo);

        return response()->json(['data' => $result]);
    }

    public function byProduct(Request $request, int $outletId): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $result = $this->purchaseByProductQuery->execute($outletId, $dateFrom, $dateTo);

        return response()->json(['data' => $result]);
    }

    public function export(ExportReportRequest $request, int $outletId): StreamedResponse
    {
        $type = $request->validated('type') ?? 'summary';
        $dateFrom = $request->validated('date_from');
        $dateTo = $request->validated('date_to');

        $data = match ($type) {
            'by_supplier' => $this->purchaseBySupplierQuery->execute($outletId, $dateFrom, $dateTo),
            'by_product' => $this->purchaseByProductQuery->execute($outletId, $dateFrom, $dateTo),
            default => [$this->purchaseSummaryQuery->execute($outletId, $dateFrom, $dateTo)],
        };

        $callback = function () use ($data, $type) {
            $file = fopen('php://output', 'w');

            // Write headers based on type
            $headers = match ($type) {
                'by_supplier' => ['supplier_id', 'supplier_name', 'total_purchase', 'outstanding_debt'],
                'by_product' => ['product_variant_id', 'product_name', 'variant_name', 'total_quantity', 'total_cost'],
                default => ['total_purchase_value', 'total_paid', 'total_outstanding_debt', 'purchase_count'],
            };

            fputcsv($file, $headers);

            // Write data rows
            foreach ($data as $row) {
                fputcsv($file, array_map(fn ($h) => $row[$h] ?? '', $headers));
            }

            fclose($file);
        };

        return response()->streamDownload($callback, "purchase-report-{$type}.csv", [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function dashboard(int $outletId): JsonResponse
    {
        $result = $this->supplierDashboardQuery->execute($outletId);

        return response()->json(['data' => $result]);
    }
}
