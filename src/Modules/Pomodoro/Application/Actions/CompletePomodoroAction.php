<?php

namespace Modules\Pomodoro\Application\Actions;

use Modules\Pomodoro\Domain\Contracts\PomodoroRepositoryInterface;
use Modules\Pomodoro\Domain\Entities\Pomodoro;

class CompletePomodoroAction
{
    public function __construct(
        private PomodoroRepositoryInterface $repository,
    ) {}

    public function execute(int $pomodoroId): Pomodoro
    {
        $pomodoro = $this->repository->findById($pomodoroId);
        $pomodoro->complete();

        return $this->repository->save($pomodoro);
    }
}
