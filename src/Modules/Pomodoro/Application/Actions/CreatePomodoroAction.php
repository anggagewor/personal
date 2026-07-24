<?php

namespace Modules\Pomodoro\Application\Actions;

use DateTimeImmutable;
use Modules\Pomodoro\Application\DTO\PomodoroData;
use Modules\Pomodoro\Domain\Contracts\PomodoroRepositoryInterface;
use Modules\Pomodoro\Domain\Entities\Pomodoro;
use Modules\Pomodoro\Domain\Enums\PomodoroStatus;

class CreatePomodoroAction
{
    public function __construct(
        private PomodoroRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, PomodoroData $data): Pomodoro
    {
        $pomodoro = new Pomodoro(
            id: null,
            userId: $userId,
            taskId: $data->taskId,
            duration: $data->duration,
            status: PomodoroStatus::Running,
            startedAt: new DateTimeImmutable(),
        );

        return $this->repository->save($pomodoro);
    }
}
