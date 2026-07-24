<?php

namespace Modules\Calendar\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Calendar\Domain\Contracts\HolidayRepositoryInterface;

class HolidayController extends Controller
{
    public function __construct(
        private HolidayRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->query('end_date', now()->endOfYear()->toDateString());
        $nationalOnly = $request->boolean('national_only', false);

        $holidays = $nationalOnly
            ? $this->repository->getNationalByDateRange($startDate, $endDate)
            : $this->repository->getByDateRange($startDate, $endDate);

        return response()->json([
            'data' => $holidays,
        ]);
    }
}
