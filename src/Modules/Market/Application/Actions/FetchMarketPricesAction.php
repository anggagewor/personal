<?php

namespace Modules\Market\Application\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;
use Modules\Market\Domain\Entities\PriceSnapshot;

class FetchMarketPricesAction
{
    public function __construct(
        private WatchlistRepositoryInterface $watchlistRepository,
        private PriceHistoryRepositoryInterface $priceHistoryRepository,
    ) {}

    /**
     * Fetch current prices for all symbols in a user's watchlist.
     * Uses cache to respect Twelve Data rate limits.
     *
     * @return array<string, array{symbol: string, price: float, change: float, change_percent: float, previous_close: float|null}>
     */
    public function execute(int $userId): array
    {
        $apiKey = config('services.twelvedata.key');
        if (!$apiKey) {
            return [];
        }

        $items = $this->watchlistRepository->findByUser($userId);
        if (empty($items)) {
            return [];
        }

        $refreshMinutes = (int) config('services.twelvedata.refresh_interval', 15);
        $cacheKey = "market_prices:user:{$userId}";

        // Return cached data if still fresh
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Build symbols list for batch API call
        $symbols = array_map(fn($item) => $item->symbol, $items);
        $symbolString = implode(',', $symbols);

        // Twelve Data /quote endpoint supports batch (comma-separated symbols)
        // 1 credit per symbol, max 8/min on free tier
        // We chunk to max 8 symbols per request to stay within rate limit
        $allQuotes = [];
        $chunks = array_chunk($symbols, 8);

        foreach ($chunks as $chunk) {
            $response = Http::timeout(15)->get('https://api.twelvedata.com/quote', [
                'symbol' => implode(',', $chunk),
                'apikey' => $apiKey,
            ]);

            if (!$response->successful()) {
                Log::warning('[Market] Twelve Data API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                continue;
            }

            $data = $response->json();

            // Single symbol returns object, multiple returns keyed object
            if (count($chunk) === 1) {
                $symbol = $chunk[0];
                if (isset($data['close'])) {
                    $allQuotes[$symbol] = $data;
                }
            } else {
                foreach ($chunk as $symbol) {
                    if (isset($data[$symbol]['close'])) {
                        $allQuotes[$symbol] = $data[$symbol];
                    }
                }
            }
        }

        // Transform and save to history
        $result = [];
        $snapshots = [];
        $now = new \DateTimeImmutable();

        foreach ($allQuotes as $symbol => $quote) {
            $price = (float) ($quote['close'] ?? 0);
            $change = (float) ($quote['change'] ?? 0);
            $changePercent = (float) ($quote['percent_change'] ?? 0);
            $previousClose = isset($quote['previous_close']) ? (float) $quote['previous_close'] : null;

            $result[$symbol] = [
                'symbol' => $symbol,
                'price' => $price,
                'change' => $change,
                'change_percent' => $changePercent,
                'previous_close' => $previousClose,
            ];

            $snapshots[] = new PriceSnapshot(
                id: null,
                userId: $userId,
                symbol: $symbol,
                price: $price,
                change: $change,
                changePercent: $changePercent,
                previousClose: $previousClose,
                fetchedAt: $now,
            );
        }

        // Save to DB history
        if (!empty($snapshots)) {
            $this->priceHistoryRepository->saveBatch($snapshots);
        }

        // Cache results
        Cache::put($cacheKey, $result, now()->addMinutes($refreshMinutes));

        return $result;
    }
}
