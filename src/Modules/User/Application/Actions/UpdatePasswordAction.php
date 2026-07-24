<?php

namespace Modules\User\Application\Actions;

use Illuminate\Support\Facades\Hash;
use Modules\User\Domain\Contracts\UserRepositoryInterface;

class UpdatePasswordAction
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, string $password): void
    {
        $this->repository->updatePassword($userId, Hash::make($password));
    }
}
