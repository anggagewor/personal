<?php

namespace Modules\Task\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Task\Application\Actions\CreateTaskAction;
use Modules\Task\Application\Actions\DeleteTaskAction;
use Modules\Task\Application\Actions\ReorderTasksAction;
use Modules\Task\Application\Actions\UpdateTaskAction;
use Modules\Task\Application\DTO\TaskData;
use Modules\Task\Domain\Contracts\TaskRepositoryInterface;
use Modules\Task\Domain\Enums\TaskPriority;
use Modules\Task\Domain\Enums\TaskStatus;
use Modules\Task\Infrastructure\Requests\StoreTaskRequest;
use Modules\Task\Infrastructure\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status') ? TaskStatus::from($request->query('status')) : null;
        $priority = $request->query('priority') ? TaskPriority::from($request->query('priority')) : null;

        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            status: $status,
            priority: $priority,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreTaskRequest $request, CreateTaskAction $action): JsonResponse
    {
        $task = $action->execute(
            userId: $request->user()->id,
            data: TaskData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $task,
            'message' => 'Task berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateTaskRequest $request, int $id, UpdateTaskAction $action): JsonResponse
    {
        $task = $this->repository->findById($id);

        if (!$task || $task->userId !== $request->user()->id) {
            abort(403);
        }

        $task = $action->execute(
            taskId: $id,
            data: TaskData::fromArray(array_merge(
                [
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status->value,
                    'priority' => $task->priority->value,
                    'due_date' => $task->dueDate?->format('Y-m-d'),
                    'position' => $task->position,
                ],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $task,
            'message' => 'Task berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteTaskAction $action): JsonResponse
    {
        $task = $this->repository->findById($id);

        if (!$task || $task->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Task berhasil dihapus.',
        ]);
    }

    public function reorder(Request $request, ReorderTasksAction $action): JsonResponse
    {
        $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);

        $action->execute(
            userId: $request->user()->id,
            orderedIds: $request->input('ordered_ids'),
        );

        return response()->json([
            'message' => 'Urutan task berhasil diperbarui.',
        ]);
    }
}
