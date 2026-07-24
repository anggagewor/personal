<?php

namespace Modules\Activity\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Activity\Domain\Contracts\ActivityLogRepositoryInterface;

class ActivityLogController extends Controller
{
    public function __construct(
        private ActivityLogRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }
}
