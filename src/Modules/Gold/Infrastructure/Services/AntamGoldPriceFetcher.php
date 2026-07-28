<?php

namespace Modules\Gold\Infrastructure\Services;

use Illuminate\Support\Facades\Process;
use Modules\Gold\Domain\Contracts\GoldPriceFetcherInterface;

class AntamGoldPriceFetcher implements GoldPriceFetcherInterface
{
    public function fetchCurrentPrice(): ?int
    {
        $script = base_path('scripts/fetch-gold-price.mjs');

        $result = Process::timeout(60)
            ->env([
                'ANTAM_CAPTCHA_URL' => config('services.antam.captcha_url'),
                'ANTAM_API_URL' => config('services.antam.api_url'),
            ])
            ->run(['node', $script]);

        if (! $result->successful()) {
            $error = $this->parseError($result->output()) ?? $result->errorOutput();

            throw new \RuntimeException('Gold price fetch failed: ' . $error);
        }

        $data = json_decode($result->output(), true);

        if (! $data || isset($data['error'])) {
            throw new \RuntimeException(
                'Gold price fetch failed: ' . ($data['error'] ?? 'Unknown error')
            );
        }

        $price = $data['price'] ?? null;

        return $price && $price > 0 ? (int) $price : null;
    }

    private function parseError(string $output): ?string
    {
        $data = json_decode($output, true);

        return $data['error'] ?? null;
    }
}
