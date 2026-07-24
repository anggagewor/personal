<?php

namespace Modules\User\Application\Actions;

use Modules\User\Domain\Contracts\UserRepositoryInterface;

class UpdatePreferencesAction
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, array $preferences): array
    {
        return $this->repository->updatePreferences($userId, $preferences);
    }
}
