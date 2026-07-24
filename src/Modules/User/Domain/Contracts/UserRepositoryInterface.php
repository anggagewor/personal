<?php

namespace Modules\User\Domain\Contracts;

use Modules\User\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function save(User $user): User;

    public function updatePreferences(int $userId, array $preferences): array;

    public function updateAvatar(int $userId, ?string $path): void;

    public function updatePassword(int $userId, string $hashedPassword): void;
}
