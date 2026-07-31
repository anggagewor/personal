<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pos\Application\Queries\DailySalesReportQuery;
use Modules\Pos\Application\Queries\DateRangeSalesReportQuery;
use Modules\Pos\Application\Queries\ProductRankingQuery;
use Modules\Pos\Application\Queries\RevenueByPaymentMethodQuery;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;
use Modules\Pos\Infrastructure\Resources\ReportResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
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

        $from = $request->query('from', now()->subDays(7)->toDateString());
        $to = $request->query('to', now()->toDateString());
        $data = $query->execute($outletId, $from, $to);

        return response()->json([
            'data' => ReportResource::toArray($data),
            'message' => 'Laporan rentang tanggal berhasil diambil.',
        ]);
    }

    public function products(Request $request, int $outletId, ProductRankingQuery $query): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());
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

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());
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

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());
        $data = $query->execute($outletId, $from, $to);

        $filename = "laporan-penjualan-{$from}-{$to}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Tanggal', 'Total Transaksi', 'Pendapatan', 'Rata-rata Transaksi']);

            $dailyData = $data['daily_breakdown'] ?? [];
            foreach ($dailyData as $row) {
                fputcsv($handle, [
                    $row['date'] ?? '',
                    $row['transaction_count'] ?? 0,
                    $row['revenue'] ?? 0,
                    $row['average_transaction'] ?? 0,
                ]);
            }

            // Summary row
            fputcsv($handle, []);
            fputcsv($handle, ['Total', $data['transaction_count'] ?? 0, $data['total_revenue'] ?? 0, $data['average_transaction'] ?? 0]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
