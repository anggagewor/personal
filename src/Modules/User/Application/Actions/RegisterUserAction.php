<?php

namespace Modules\User\Application\Actions;

use Modules\User\Application\DTO\UserData;
use Modules\User\Domain\Contracts\UserRepositoryInterface;
use Modules\User\Domain\Entities\User;

class RegisterUserAction
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    public function execute(UserData $data): User
    {
        $user = new User(
            id: null,
            name: $data->name,
            email: $data->email,
            password: $data->password,
        );

        return $this->repository->save($user);
    }
}
