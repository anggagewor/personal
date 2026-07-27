<?php

namespace Modules\Market\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Market\Application\Actions\AddWatchlistItemAction;
use Modules\Market\Application\Actions\FetchMarketPricesAction;
use Modules\Market\Application\Actions\GetPriceHistoryAction;
use Modules\Market\Application\Actions\RemoveWatchlistItemAction;
use Modules\Market\Application\DTO\WatchlistItemData;
use Modules\Market\Domain\Contracts\PriceHistoryRepositoryInterface;
use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;
use Modules\Market\Infrastructure\Requests\StoreWatchlistItemRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

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
        $limit = (int) $request->query('limit', 50);
        $limit = min($limit, 100);

        $history = $action->execute(
            userId: $request->user()->id,
            symbol: strtoupper($symbol),
            limit: $limit,
        );

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
}
