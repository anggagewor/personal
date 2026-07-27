<?php

namespace Modules\Gold\Infrastructure\Commands;

use Illuminate\Console\Command;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;
use Modules\Gold\Domain\Entities\GoldPrice;

class ImportGoldHistoryCommand extends Command
{
    protected $signature = 'gold:import-history {--file=storage/antam.json : Path to JSON file}';

    protected $description = 'Import historical Antam gold prices from JSON file into database';

    public function handle(GoldPriceRepositoryInterface $repository): int
    {
        $file = base_path($this->option('file'));

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $this->info("Reading {$file}...");
        $raw = json_decode(file_get_contents($file), true);

        if (!is_array($raw)) {
            $this->error('Invalid JSON format.');
            return self::FAILURE;
        }

        $this->info(sprintf('Found %d data points. Processing...', count($raw)));

        // Deduplicate by date (keep last entry per date)
        $byDate = [];
        foreach ($raw as [$timestampMs, $price]) {
            $date = date('Y-m-d', (int) ($timestampMs / 1000));
            $byDate[$date] = (int) $price;
        }

        ksort($byDate);
        $this->info(sprintf('Unique dates: %d', count($byDate)));

        // Build entities with change calculation
        $entities = [];
        $prevPrice = null;

        foreach ($byDate as $date => $price) {
            $change = $prevPrice !== null ? $price - $prevPrice : 0;
            $changePercent = ($prevPrice !== null && $prevPrice > 0)
                ? round(($change / $prevPrice) * 100, 4)
                : 0;

            $entities[] = new GoldPrice(
                id: null,
                date: $date,
                price: $price,
                change: $change,
                changePercent: $changePercent,
            );

            $prevPrice = $price;
        }

        $this->info('Saving to database...');
        $repository->saveBatch($entities);

        $this->info(sprintf('Done. Imported %d records.', count($entities)));
        return self::SUCCESS;
    }
}
