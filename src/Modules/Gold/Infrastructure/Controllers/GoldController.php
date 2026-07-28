<?php

namespace Modules\Gold\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gold\Application\Actions\ExportGoldPricesAction;
use Modules\Gold\Application\Actions\GetGoldDashboardAction;
use Modules\Gold\Application\Actions\ImportGoldPricesAction;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoldController extends Controller
{
    public function __construct(
        private GoldPriceRepositoryInterface $repository,
    ) {}

    /**
     * Dashboard data: latest + sparkline + 30d stats.
     */
    public function dashboard(GetGoldDashboardAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute(),
        ]);
    }

    /**
     * Full history (paginated via limit/period).
     */
    public function history(Request $request): JsonResponse
    {
        $period = $request->query('period', '1y'); // 1m, 3m, 6m, 1y, 5y, all

        $limit = match ($period) {
            '1m' => 30,
            '3m' => 90,
            '6m' => 180,
            '1y' => 365,
            '5y' => 1825,
            'all' => 99999,
            default => 365,
        };

        $history = $this->repository->getHistory($limit);

        return response()->json([
            'data' => array_map(fn($p) => [
                'date' => $p->date,
                'price' => $p->price,
                'change' => $p->change,
                'change_percent' => $p->changePercent,
            ], $history),
            'meta' => [
                'total' => $this->repository->count(),
                'period' => $period,
            ],
        ]);
    }

    /**
     * Export gold prices as CSV or JSON.
     */
    public function export(Request $request, ExportGoldPricesAction $action): StreamedResponse|JsonResponse
    {
        $format = $request->query('format', 'csv');
        $from = $request->query('from');
        $to = $request->query('to');

        $rows = $action->execute($from, $to);

        if ($format === 'json') {
            return response()->json(['data' => $rows]);
        }

        return $this->streamCsv($rows, 'gold-prices.csv', ['date', 'price', 'change', 'change_percent']);
    }

    /**
     * Import gold prices from CSV file.
     */
    public function import(Request $request, ImportGoldPricesAction $action): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parseCsv($request->file('file'));

        $count = $action->execute($rows);

        return response()->json([
            'message' => "Berhasil mengimpor {$count} data harga emas.",
            'data' => ['imported' => $count],
        ]);
    }

    /**
     * Download CSV import template.
     */
    public function template(): StreamedResponse
    {
        $sample = [
            ['date' => '2026-01-01', 'price' => '1500000', 'change' => '10000', 'change_percent' => '0.67'],
            ['date' => '2026-01-02', 'price' => '1510000', 'change' => '10000', 'change_percent' => '0.67'],
        ];

        return $this->streamCsv($sample, 'gold-import-template.csv', ['date', 'price', 'change', 'change_percent']);
    }

    private function streamCsv(array $rows, string $filename, array $headers): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn($h) => $row[$h] ?? '', $headers));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function parseCsv(\Illuminate\Http\UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) !== count($header)) {
                continue;
            }

            $rows[] = array_combine($header, $line);
        }

        fclose($handle);

        return $rows;
    }
}
