<?php

namespace Modules\Pos\Infrastructure\Repositories;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Modules\Pos\Domain\Contracts\ReportRepositoryInterface;
use Modules\Pos\Infrastructure\Models\TransactionModel;

class EloquentReportRepository implements ReportRepositoryInterface
{
    public function getDailySummary(int $outletId, string $date): array
    {
        $result = TransactionModel::where('outlet_id', $outletId)
            ->whereDate('created_at', $date)
            ->where('status', '!=', 'voided')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue, COUNT(*) as count, COALESCE(AVG(total), 0) as average')
            ->first();

        return [
            'date' => $date,
            'revenue' => (float) ($result->revenue ?? 0),
            'count' => (int) ($result->count ?? 0),
            'average' => (float) ($result->average ?? 0),
        ];
    }

    public function getDateRangeSummary(int $outletId, string $from, string $to): array
    {
        $results = TransactionModel::where('outlet_id', $outletId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->where('status', '!=', 'voided')
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue, COUNT(*) as count, COALESCE(AVG(total), 0) as average')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $period = CarbonPeriod::create($from, $to);
        $summary = [];

        foreach ($period as $day) {
            $dateStr = $day->format('Y-m-d');
            $row = $results->get($dateStr);

            $summary[] = [
                'date' => $dateStr,
                'revenue' => (float) ($row->revenue ?? 0),
                'count' => (int) ($row->count ?? 0),
                'average' => (float) ($row->average ?? 0),
            ];
        }

        return $summary;
    }

    public function getProductRanking(int $outletId, string $from, string $to, int $limit): array
    {
        return DB::table('pos_transaction_items as ti')
            ->join('pos_transactions as t', 't.id', '=', 'ti.transaction_id')
            ->where('t.outlet_id', $outletId)
            ->whereBetween(DB::raw('DATE(t.created_at)'), [$from, $to])
            ->where('t.status', '!=', 'voided')
            ->select([
                'ti.product_id',
                'ti.product_name',
                DB::raw('SUM(ti.quantity) as total_quantity'),
                DB::raw('SUM(ti.subtotal) as total_revenue'),
            ])
            ->groupBy('ti.product_id', 'ti.product_name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'total_quantity' => (int) $row->total_quantity,
                'total_revenue' => (float) $row->total_revenue,
            ])
            ->all();
    }

    public function getRevenueByPaymentMethod(int $outletId, string $from, string $to): array
    {
        return TransactionModel::where('outlet_id', $outletId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->where('status', '!=', 'voided')
            ->select([
                'payment_method_type',
                DB::raw('COALESCE(SUM(total), 0) as total_revenue'),
                DB::raw('COUNT(*) as count'),
            ])
            ->groupBy('payment_method_type')
            ->get()
            ->map(fn ($row) => [
                'payment_method_type' => $row->payment_method_type,
                'total_revenue' => (float) $row->total_revenue,
                'count' => (int) $row->count,
            ])
            ->all();
    }

    public function getDashboardStats(int $outletId): array
    {
        $today = Carbon::today()->format('Y-m-d');

        $todayStats = TransactionModel::where('outlet_id', $outletId)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'voided')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue, COUNT(*) as count')
            ->first();

        $sevenDaysAgo = Carbon::today()->subDays(6)->format('Y-m-d');

        $trendResults = TransactionModel::where('outlet_id', $outletId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$sevenDaysAgo, $today])
            ->where('status', '!=', 'voided')
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $period = CarbonPeriod::create($sevenDaysAgo, $today);
        $trend = [];

        foreach ($period as $day) {
            $dateStr = $day->format('Y-m-d');
            $row = $trendResults->get($dateStr);
            $trend[] = [
                'date' => $dateStr,
                'revenue' => (float) ($row->revenue ?? 0),
            ];
        }

        return [
            'today_revenue' => (float) ($todayStats->revenue ?? 0),
            'today_count' => (int) ($todayStats->count ?? 0),
            'trend' => $trend,
        ];
    }
}
