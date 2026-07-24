<?php

namespace Modules\Pomodoro\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pomodoro\Application\Actions\CancelPomodoroAction;
use Modules\Pomodoro\Application\Actions\CompletePomodoroAction;
use Modules\Pomodoro\Application\Actions\CreatePomodoroAction;
use Modules\Pomodoro\Application\DTO\PomodoroData;
use Modules\Pomodoro\Domain\Contracts\PomodoroRepositoryInterface;
use Modules\Pomodoro\Infrastructure\Requests\StorePomodoroRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class PomodoroController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private PomodoroRepositoryInterface $repository,
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

    public function store(StorePomodoroRequest $request, CreatePomodoroAction $action): JsonResponse
    {
        $pomodoro = $action->execute(
            userId: $request->user()->id,
            data: PomodoroData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $pomodoro,
            'message' => 'Pomodoro dimulai.',
        ], 201);
    }

    public function complete(Request $request, int $id, CompletePomodoroAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $pomodoro = $action->execute($id);

        return response()->json([
            'data' => $pomodoro,
            'message' => 'Pomodoro selesai.',
        ]);
    }

    public function cancel(Request $request, int $id, CancelPomodoroAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $pomodoro = $action->execute($id);

        return response()->json([
            'data' => $pomodoro,
            'message' => 'Pomodoro dibatalkan.',
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->repository->getStats($request->user()->id);

        return response()->json([
            'data' => $stats,
        ]);
    }
}
