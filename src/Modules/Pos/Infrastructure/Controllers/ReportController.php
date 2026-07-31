<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Queries\DailySalesReportQuery;
use Modules\Pos\Application\Queries\DateRangeSalesReportQuery;
use Modules\Pos\Application\Queries\ProductRankingQuery;
use Modules\Pos\Application\Queries\RevenueByPaymentMethodQuery;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;
use Modules\Pos\Infrastructure\Resources\ReportResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private OutletRepositoryInterface $outletRepository,
        private ReportRepositoryInterface $reportRepository,
    ) {}

    public function daily(Request $request, int $outletId, DailySalesReportQuery $query): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $date = $request->query('date', now()->toDateString());
        $data = $query->execute($outletId, $date);

        return response()->json([
            'data' => ReportResource::toArray($data),
            'message' => 'Laporan harian berhasil diambil.',
        ]);
    }

    public function range(Request $request, int $outletId, DateRangeSalesReportQuery $query): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $from = $request->query('start_date', now()->subDays(7)->toDateString());
        $to = $request->query('end_date', now()->toDateString());
        $data = $query->execute($outletId, $from, $to);

        return response()->json([
            'data' => $data,
            'message' => 'Laporan rentang tanggal berhasil diambil.',
        ]);
    }

    public function products(Request $request, int $outletId, ProductRankingQuery $query): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $from = $request->query('start_date', now()->subDays(30)->toDateString());
        $to = $request->query('end_date', now()->toDateString());
        $limit = (int) $request->query('limit', 10);
        $data = $query->execute($outletId, $from, $to, $limit);

        return response()->json([
            'data' => ReportResource::collection($data),
            'message' => 'Peringkat produk berhasil diambil.',
        ]);
    }

    public function payments(Request $request, int $outletId, RevenueByPaymentMethodQuery $query): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $from = $request->query('start_date', now()->subDays(30)->toDateString());
        $to = $request->query('end_date', now()->toDateString());
        $data = $query->execute($outletId, $from, $to);

        return response()->json([
            'data' => ReportResource::collection($data),
            'message' => 'Pendapatan per metode pembayaran berhasil diambil.',
        ]);
    }

    public function dashboard(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $data = $this->reportRepository->getDashboardStats($outletId);

        return response()->json([
            'data' => ReportResource::toArray($data),
            'message' => 'Dashboard berhasil diambil.',
        ]);
    }

    public function export(Request $request, int $outletId, DateRangeSalesReportQuery $query): StreamedResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $from = $request->query('start_date', now()->subDays(30)->toDateString());
        $to = $request->query('end_date', now()->toDateString());
        $data = $query->execute($outletId, $from, $to);

        $filename = "laporan-penjualan-{$from}-{$to}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Tanggal', 'Jumlah Transaksi', 'Penjualan Kotor', 'Total Diskon', 'Pendapatan Bersih', 'Rata-rata Transaksi']);

            $totalGross = 0;
            $totalDiscount = 0;
            $totalRevenue = 0;
            $totalCount = 0;

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row['date'] ?? '',
                    $row['count'] ?? 0,
                    $row['gross_revenue'] ?? 0,
                    $row['discount'] ?? 0,
                    $row['revenue'] ?? 0,
                    $row['average'] ?? 0,
                ]);
                $totalGross += $row['gross_revenue'] ?? 0;
                $totalDiscount += $row['discount'] ?? 0;
                $totalRevenue += $row['revenue'] ?? 0;
                $totalCount += $row['count'] ?? 0;
            }

            // Summary row
            fputcsv($handle, []);
            fputcsv($handle, ['Total', $totalCount, $totalGross, $totalDiscount, $totalRevenue, $totalCount > 0 ? round($totalRevenue / $totalCount) : 0]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
