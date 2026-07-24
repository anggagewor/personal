<?php

namespace Modules\Goal\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Goal\Application\Actions\AddMilestoneAction;
use Modules\Goal\Application\Actions\CreateGoalAction;
use Modules\Goal\Application\Actions\DeleteGoalAction;
use Modules\Goal\Application\Actions\ToggleMilestoneAction;
use Modules\Goal\Application\Actions\UpdateGoalAction;
use Modules\Goal\Application\DTO\GoalData;
use Modules\Goal\Domain\Contracts\GoalRepositoryInterface;
use Modules\Goal\Infrastructure\Requests\StoreGoalRequest;
use Modules\Goal\Infrastructure\Requests\UpdateGoalRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class GoalController extends Controller
{
    use AuthorizesOwnership;
    public function __construct(
        private GoalRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $goals = $this->repository->findByUser($request->user()->id);

        return response()->json([
            'data' => $goals,
        ]);
    }

    public function store(StoreGoalRequest $request, CreateGoalAction $action): JsonResponse
    {
        $goal = $action->execute(
            userId: $request->user()->id,
            data: GoalData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $goal,
            'message' => 'Goal berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateGoalRequest $request, int $id, UpdateGoalAction $action): JsonResponse
    {
        $goal = $this->findOwnedOrFail($this->repository, $id, $request);

        $goal = $action->execute(
            goalId: $id,
            data: GoalData::fromArray(array_merge(
                [
                    'title' => $goal->title,
                    'description' => $goal->description,
                    'target_date' => $goal->targetDate?->format('Y-m-d'),
                    'status' => $goal->status->value,
                ],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $goal,
            'message' => 'Goal berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteGoalAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Goal berhasil dihapus.',
        ]);
    }

    public function addMilestone(Request $request, int $id, AddMilestoneAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $goal = $action->execute($id, $request->input('title'));

        return response()->json([
            'data' => $goal,
            'message' => 'Milestone berhasil ditambahkan.',
        ], 201);
    }

    public function toggleMilestone(Request $request, int $id, int $milestoneId, ToggleMilestoneAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $goal = $action->execute($id, $milestoneId);

        return response()->json([
            'data' => $goal,
            'message' => 'Milestone berhasil diupdate.',
        ]);
    }
}
