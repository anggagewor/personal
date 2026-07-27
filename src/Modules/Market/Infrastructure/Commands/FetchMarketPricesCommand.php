<?php

namespace Modules\Market\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Entities\PriceSnapshot;
use Modules\Market\Infrastructure\Models\WatchlistItemModel;

class FetchMarketPricesCommand extends Command
{
    protected $signature = 'market:fetch-prices';

    protected $description = 'Fetch current prices from Twelve Data for all users\' watchlist symbols and store in history';

    public function handle(PriceHistoryRepositoryInterface $priceRepo): int
    {
        $apiKey = config('services.twelvedata.key');

        if (!$apiKey) {
            $this->warn('TWELVEDATA_API_KEY not configured. Skipping.');
            return self::SUCCESS;
        }

        // Get all unique symbols across all users (with user mapping)
        $allItems = WatchlistItemModel::select('user_id', 'symbol')->get();

        if ($allItems->isEmpty()) {
            $this->info('No watchlist items found. Nothing to fetch.');
            return self::SUCCESS;
        }

        // Get unique symbols to minimize API calls
        $uniqueSymbols = $allItems->pluck('symbol')->unique()->values()->all();

        $this->info(sprintf('Fetching prices for %d unique symbols...', count($uniqueSymbols)));

        // Chunk by 8 (Twelve Data free tier: 8 credits/min)
        $allQuotes = [];
        $chunks = array_chunk($uniqueSymbols, 8);

        foreach ($chunks as $index => $chunk) {
            // Rate limit: wait between chunks (except first)
            if ($index > 0) {
                $this->info('Rate limit pause (60s)...');
                sleep(60);
            }

            $response = Http::timeout(20)->get('https://api.twelvedata.com/quote', [
                'symbol' => implode(',', $chunk),
                'apikey' => $apiKey,
            ]);

            if (!$response->successful()) {
                Log::warning('[Market] Twelve Data API error in cron', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->error('API request failed for chunk: ' . implode(', ', $chunk));
                continue;
            }

            $data = $response->json();

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

        if (empty($allQuotes)) {
            $this->warn('No quotes received from API.');
            return self::SUCCESS;
        }

        // Build user→symbol mapping
        $userSymbolMap = [];
        foreach ($allItems as $item) {
            $userSymbolMap[$item->user_id][] = $item->symbol;
        }

        // Save snapshots per user
        $now = new \DateTimeImmutable();
        $snapshots = [];

        foreach ($userSymbolMap as $userId => $symbols) {
            foreach ($symbols as $symbol) {
                if (!isset($allQuotes[$symbol])) {
                    continue;
                }

                $quote = $allQuotes[$symbol];
                $snapshots[] = new PriceSnapshot(
                    id: null,
                    userId: $userId,
                    symbol: $symbol,
                    price: (float) ($quote['close'] ?? 0),
                    change: (float) ($quote['change'] ?? 0),
                    changePercent: (float) ($quote['percent_change'] ?? 0),
                    previousClose: isset($quote['previous_close']) ? (float) $quote['previous_close'] : null,
                    fetchedAt: $now,
                );
            }
        }

        if (!empty($snapshots)) {
            $priceRepo->saveBatch($snapshots);
        }

        $this->info(sprintf('Done. Saved %d price snapshots for %d symbols.', count($snapshots), count($allQuotes)));

        // Prune old data (keep 30 days)
        $pruned = $priceRepo->pruneOlderThan(30);
        if ($pruned > 0) {
            $this->info(sprintf('Pruned %d old history records.', $pruned));
        }

        return self::SUCCESS;
    }
}
