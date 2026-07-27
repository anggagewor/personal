<?php

namespace Modules\Gold\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gold\Application\Actions\GetGoldDashboardAction;
use Modules\Gold\Domain\Contracts\GoldPriceRepositoryInterface;

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
}
