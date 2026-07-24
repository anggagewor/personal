<?php

namespace Modules\User\Application\Actions;

use Modules\User\Domain\Contracts\UserRepositoryInterface;
use Modules\User\Domain\Entities\User;

class UpdateProfileAction
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, array $data): User
    {
        $user = $this->repository->findById($userId);

        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;

        return $this->repository->save($user);
    }
}
