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
            ->selectRaw('COALESCE(SUM(subtotal), 0) as gross_revenue, COALESCE(SUM(discount_amount), 0) as total_discount, COALESCE(SUM(total), 0) as revenue, COUNT(*) as count, COALESCE(AVG(total), 0) as average')
            ->first();

        $topProducts = DB::table('pos_transaction_items as ti')
            ->join('pos_transactions as t', 't.id', '=', 'ti.transaction_id')
            ->where('t.outlet_id', $outletId)
            ->whereDate('t.created_at', $date)
            ->where('t.status', '!=', 'voided')
            ->select([
                'ti.product_name as name',
                DB::raw('SUM(ti.quantity) as quantity'),
                DB::raw('SUM(ti.subtotal) as revenue'),
            ])
            ->groupBy('ti.product_name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (int) $row->quantity,
                'revenue' => (float) $row->revenue,
            ])
            ->all();

        return [
            'date' => $date,
            'gross_revenue' => (float) ($result->gross_revenue ?? 0),
            'total_discount' => (float) ($result->total_discount ?? 0),
            'total_revenue' => (float) ($result->revenue ?? 0),
            'transaction_count' => (int) ($result->count ?? 0),
            'average_transaction' => (float) ($result->average ?? 0),
            'top_products' => $topProducts,
        ];
    }

    public function getDateRangeSummary(int $outletId, string $from, string $to): array
    {
        $results = TransactionModel::where('outlet_id', $outletId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->where('status', '!=', 'voided')
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(subtotal), 0) as gross_revenue, COALESCE(SUM(discount_amount), 0) as discount, COALESCE(SUM(total), 0) as revenue, COUNT(*) as count, COALESCE(AVG(total), 0) as average')
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
                'gross_revenue' => (float) ($row->gross_revenue ?? 0),
                'discount' => (float) ($row->discount ?? 0),
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
                'quantity_sold' => (int) $row->total_quantity,
                'revenue' => (float) $row->total_revenue,
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
            ->selectRaw('COALESCE(SUM(subtotal), 0) as gross_revenue, COALESCE(SUM(discount_amount), 0) as discount, COALESCE(SUM(total), 0) as revenue, COUNT(*) as count')
            ->first();

        $sevenDaysAgo = Carbon::today()->subDays(6)->format('Y-m-d');

        $trendResults = TransactionModel::where('outlet_id', $outletId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$sevenDaysAgo, $today])
            ->where('status', '!=', 'voided')
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(discount_amount), 0) as discount')
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
                'discount' => (float) ($row->discount ?? 0),
            ];
        }

        return [
            'today_revenue' => (float) ($todayStats->revenue ?? 0),
            'today_gross_revenue' => (float) ($todayStats->gross_revenue ?? 0),
            'today_discount' => (float) ($todayStats->discount ?? 0),
            'today_transactions' => (int) ($todayStats->count ?? 0),
            'weekly_trend' => $trend,
        ];
    }
}
