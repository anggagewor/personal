<?php

namespace Modules\Gold\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Domain\Entities\GoldPrice;

class FetchGoldDailyCommand extends Command
{
    protected $signature = 'gold:fetch-daily';

    protected $description = 'Fetch today\'s Antam gold price and store in database';

    public function handle(GoldPriceRepositoryInterface $repository): int
    {
        $apiUrl = config('services.antam.api_url');

        if (!$apiUrl) {
            $this->warn('ANTAM_API_URL not configured. Skipping.');
            return self::SUCCESS;
        }

        $this->info('Fetching Antam gold price...');

        try {
            $response = Http::timeout(15)->get($apiUrl);

            if (!$response->successful()) {
                $this->error('API request failed: ' . $response->status());
                Log::warning('[Gold] Antam API error', ['status' => $response->status()]);
                return self::FAILURE;
            }

            $data = $response->json();

            // Support multiple response formats
            $price = $this->extractPrice($data);

            if (!$price) {
                $this->error('Could not extract price from API response.');
                Log::warning('[Gold] Could not parse Antam API response', ['data' => $data]);
                return self::FAILURE;
            }

            $today = date('Y-m-d');

            // Get previous price for change calculation
            $latest = $repository->getLatest();
            $prevPrice = $latest?->price ?? 0;
            $change = $prevPrice > 0 ? $price - $prevPrice : 0;
            $changePercent = $prevPrice > 0 ? round(($change / $prevPrice) * 100, 4) : 0;

            $entity = new GoldPrice(
                id: null,
                date: $today,
                price: $price,
                change: $change,
                changePercent: $changePercent,
            );

            $repository->save($entity);

            $this->info(sprintf('Saved: %s = Rp %s (%s%s)',
                $today,
                number_format($price, 0, ',', '.'),
                $change >= 0 ? '+' : '',
                number_format($change, 0, ',', '.'),
            ));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('[Gold] Fetch daily error', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }
    }

    private function extractPrice(mixed $data): ?int
    {
        // Format 1: [[timestamp, price], ...] (same as antam.json)
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            $last = end($data);
            return isset($last[1]) ? (int) $last[1] : null;
        }

        // Format 2: { "price": 1234000 }
        if (is_array($data) && isset($data['price'])) {
            return (int) $data['price'];
        }

        // Format 3: { "data": { "price": 1234000 } }
        if (is_array($data) && isset($data['data']['price'])) {
            return (int) $data['data']['price'];
        }

        // Format 4: { "sell": 1234000 } (Antam sell price)
        if (is_array($data) && isset($data['sell'])) {
            return (int) $data['sell'];
        }

        return null;
    }
}
