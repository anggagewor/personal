<?php

namespace Modules\Gold\Infrastructure\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Gold\Application\Actions\FetchGoldDailyAction;

class FetchGoldDailyCommand extends Command
{
    protected $signature = 'gold:fetch-daily';

    protected $description = 'Fetch today\'s Antam gold price and store in database';

    public function handle(FetchGoldDailyAction $action): int
    {
        if (! config('services.antam.api_url')) {
            $this->warn('ANTAM_API_URL not configured. Skipping.');

            return self::SUCCESS;
        }

        $this->info('Fetching Antam gold price...');

        try {
            $entity = $action->execute();

            $this->info(sprintf(
                'Saved: %s = Rp %s (%s%s)',
                $entity->date,
                number_format($entity->price, 0, ',', '.'),
                $entity->change >= 0 ? '+' : '',
                number_format($entity->change, 0, ',', '.'),
            ));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            Log::error('[Gold] Fetch daily error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return self::FAILURE;
        }
    }
}
