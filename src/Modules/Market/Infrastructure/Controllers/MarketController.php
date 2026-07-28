<?php

namespace Modules\Market\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Market\Application\Actions\AddWatchlistItemAction;
use Modules\Market\Application\Actions\ExportMarketHistoryAction;
use Modules\Market\Application\Actions\FetchMarketPricesAction;
use Modules\Market\Application\Actions\GetPriceHistoryAction;
use Modules\Market\Application\Actions\ImportMarketHistoryAction;
use Modules\Market\Application\Actions\RemoveWatchlistItemAction;
use Modules\Market\Application\DTO\WatchlistItemData;
use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;
use Modules\Market\Infrastructure\Requests\StoreWatchlistItemRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private WatchlistRepositoryInterface $repository,
    ) {}

    /**
     * Get user's watchlist items.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->repository->findByUser($request->user()->id);

        return response()->json([
            'data' => array_map(fn($item) => [
                'id' => $item->id,
                'symbol' => $item->symbol,
                'type' => $item->type,
                'label' => $item->label,
                'position' => $item->position,
            ], $items),
        ]);
    }

    /**
     * Add a symbol to watchlist.
     */
    public function store(StoreWatchlistItemRequest $request, AddWatchlistItemAction $action): JsonResponse
    {
        try {
            $item = $action->execute(
                userId: $request->user()->id,
                data: WatchlistItemData::fromArray($request->validated()),
            );

            return response()->json([
                'data' => [
                    'id' => $item->id,
                    'symbol' => $item->symbol,
                    'type' => $item->type,
                    'label' => $item->label,
                    'position' => $item->position,
                ],
                'message' => 'Simbol berhasil ditambahkan ke watchlist.',
            ], 201);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a symbol from watchlist.
     */
    public function destroy(Request $request, int $id, RemoveWatchlistItemAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Simbol berhasil dihapus dari watchlist.',
        ]);
    }

    /**
     * Get current prices for all watchlist symbols (from DB, no live API call).
     */
    public function prices(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $priceRepo = app(PriceHistoryRepositoryInterface::class);
        $latestPrices = $priceRepo->getLatestPrices($userId);

        $result = [];
        foreach ($latestPrices as $symbol => $snapshot) {
            $result[$symbol] = [
                'symbol' => $snapshot->symbol,
                'price' => $snapshot->price,
                'change' => $snapshot->change,
                'change_percent' => $snapshot->changePercent,
                'previous_close' => $snapshot->previousClose,
            ];
        }

        return response()->json([
            'data' => $result,
            'meta' => [
                'refresh_interval' => (int) config('services.twelvedata.refresh_interval', 15),
            ],
        ]);
    }

    /**
     * Get price history for a specific symbol.
     */
    public function history(Request $request, string $symbol, GetPriceHistoryAction $action): JsonResponse
    {
        $from = $request->query('from');
        $to = $request->query('to');

        if ($from && $to) {
            $history = $action->execute(
                userId: $request->user()->id,
                symbol: strtoupper($symbol),
                from: $from,
                to: $to,
            );
        } else {
            $limit = (int) $request->query('limit', 50);
            $limit = min($limit, 100);

            $history = $action->execute(
                userId: $request->user()->id,
                symbol: strtoupper($symbol),
                limit: $limit,
            );
        }

        return response()->json([
            'data' => array_map(fn($s) => [
                'price' => $s->price,
                'change' => $s->change,
                'change_percent' => $s->changePercent,
                'fetched_at' => $s->fetchedAt?->format('Y-m-d H:i:s'),
            ], $history),
        ]);
    }

    /**
     * Get market config (refresh interval limits).
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'data' => [
                'refresh_interval' => (int) config('services.twelvedata.refresh_interval', 15),
                'max_symbols' => 15,
                'api_configured' => !empty(config('services.twelvedata.key')),
            ],
        ]);
    }

    /**
     * Dashboard endpoint: returns watchlist items + latest prices + sparkline data.
     * All from DB — no live API call.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $items = $this->repository->findByUser($userId);

        if (empty($items)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $priceRepo = app(PriceHistoryRepositoryInterface::class);
        $latestPrices = $priceRepo->getLatestPrices($userId);
        $sparklines = $priceRepo->getSparklines($userId, 20);

        $result = array_map(fn($item) => [
            'id' => $item->id,
            'symbol' => $item->symbol,
            'type' => $item->type,
            'label' => $item->label,
            'price' => isset($latestPrices[$item->symbol]) ? $latestPrices[$item->symbol]->price : null,
            'change' => isset($latestPrices[$item->symbol]) ? $latestPrices[$item->symbol]->change : null,
            'change_percent' => isset($latestPrices[$item->symbol]) ? $latestPrices[$item->symbol]->changePercent : null,
            'previous_close' => isset($latestPrices[$item->symbol]) ? $latestPrices[$item->symbol]->previousClose : null,
            'sparkline' => $sparklines[$item->symbol] ?? [],
        ], $items);

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Export market price history as CSV or JSON.
     */
    public function export(Request $request, ExportMarketHistoryAction $action): StreamedResponse|JsonResponse
    {
        $format = $request->query('format', 'csv');
        $from = $request->query('from');
        $to = $request->query('to');

        $rows = $action->execute($request->user()->id, $from, $to);

        if ($format === 'json') {
            return response()->json(['data' => $rows]);
        }

        return $this->streamCsv($rows, 'market-history.csv', ['symbol', 'price', 'change', 'change_percent', 'fetched_at']);
    }

    /**
     * Import market price history from CSV file.
     */
    public function import(Request $request, ImportMarketHistoryAction $action): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->parseCsv($request->file('file'));

        $count = $action->execute($request->user()->id, $rows);

        return response()->json([
            'message' => "Berhasil mengimpor {$count} data riwayat harga.",
            'data' => ['imported' => $count],
        ]);
    }

    /**
     * Download CSV import template.
     */
    public function template(): StreamedResponse
    {
        $sample = [
            ['symbol' => 'USD/IDR', 'price' => '16250.50', 'change' => '50.25', 'change_percent' => '0.31', 'fetched_at' => '2026-01-01 10:00:00'],
            ['symbol' => 'BTC/USD', 'price' => '67500.00', 'change' => '1200.00', 'change_percent' => '1.81', 'fetched_at' => '2026-01-01 10:00:00'],
        ];

        return $this->streamCsv($sample, 'market-import-template.csv', ['symbol', 'price', 'change', 'change_percent', 'fetched_at']);
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
