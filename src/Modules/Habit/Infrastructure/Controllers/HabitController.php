<?php

namespace Modules\Habit\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Habit\Application\Actions\CreateHabitAction;
use Modules\Habit\Application\Actions\DeleteHabitAction;
use Modules\Habit\Application\Actions\ToggleHabitAction;
use Modules\Habit\Application\Actions\UpdateHabitAction;
use Modules\Habit\Application\DTO\HabitData;
use Modules\Habit\Domain\Contracts\HabitRepositoryInterface;
use Modules\Habit\Infrastructure\Requests\StoreHabitRequest;
use Modules\Habit\Infrastructure\Requests\UpdateHabitRequest;

class HabitController extends Controller
{
    public function __construct(
        private HabitRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $habits = $this->repository->findByUser($request->user()->id);

        return response()->json([
            'data' => $habits,
        ]);
    }

    public function store(StoreHabitRequest $request, CreateHabitAction $action): JsonResponse
    {
        $habit = $action->execute(
            userId: $request->user()->id,
            data: HabitData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $habit,
            'message' => 'Habit berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateHabitRequest $request, int $id, UpdateHabitAction $action): JsonResponse
    {
        $habit = $this->repository->findById($id);

        if (!$habit || $habit->userId !== $request->user()->id) {
            abort(403);
        }

        $habit = $action->execute(
            habitId: $id,
            data: HabitData::fromArray(array_merge(
                ['name' => $habit->name, 'frequency' => $habit->frequency],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $habit,
            'message' => 'Habit berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteHabitAction $action): JsonResponse
    {
        $habit = $this->repository->findById($id);

        if (!$habit || $habit->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Habit berhasil dihapus.',
        ]);
    }

    public function toggle(Request $request, int $id, ToggleHabitAction $action): JsonResponse
    {
        $habit = $this->repository->findById($id);

        if (!$habit || $habit->userId !== $request->user()->id) {
            abort(403);
        }

        $habit = $action->execute($id);

        return response()->json([
            'data' => $habit,
            'message' => 'Habit berhasil diupdate.',
        ]);
    }
}
