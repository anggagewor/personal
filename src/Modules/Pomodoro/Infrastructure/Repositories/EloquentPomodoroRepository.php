<?php

namespace Modules\Pomodoro\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pomodoro\Domain\Contracts\PomodoroRepositoryInterface;
use Modules\Pomodoro\Domain\Entities\Pomodoro;
use Modules\Pomodoro\Domain\Enums\PomodoroStatus;
use Modules\Pomodoro\Infrastructure\Models\PomodoroModel;

class EloquentPomodoroRepository implements PomodoroRepositoryInterface
{
    public function findById(int $id): ?Pomodoro
    {
        $model = PomodoroModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUserPaginated(int $userId, int $perPage = 15): array
    {
        $paginator = PomodoroModel::where('user_id', $userId)
            ->orderByDesc('started_at')
            ->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function save(Pomodoro $pomodoro): Pomodoro
    {
        $model = PomodoroModel::updateOrCreate(
            ['id' => $pomodoro->id],
            [
                'user_id' => $pomodoro->userId,
                'task_id' => $pomodoro->taskId,
                'duration' => $pomodoro->duration,
                'status' => $pomodoro->status->value,
                'started_at' => $pomodoro->startedAt?->format('Y-m-d H:i:s'),
                'finished_at' => $pomodoro->completedAt?->format('Y-m-d H:i:s'),
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function getStats(int $userId): array
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        return [
            'today' => PomodoroModel::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereDate('finished_at', $today)
                ->count(),
            'this_week' => PomodoroModel::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereDate('finished_at', '>=', $weekStart)
                ->count(),
            'total' => PomodoroModel::where('user_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'total_minutes' => (int) PomodoroModel::where('user_id', $userId)
                ->where('status', 'completed')
                ->sum('duration'),
        ];
    }

    private function toEntity(PomodoroModel $model): Pomodoro
    {
        return new Pomodoro(
            id: $model->id,
            userId: $model->user_id,
            taskId: $model->task_id,
            duration: (int) $model->duration,
            status: PomodoroStatus::from($model->status),
            startedAt: $model->started_at ? new DateTimeImmutable($model->started_at->toDateTimeString()) : null,
            completedAt: $model->finished_at ? new DateTimeImmutable($model->finished_at->toDateTimeString()) : null,
        );
    }
}
