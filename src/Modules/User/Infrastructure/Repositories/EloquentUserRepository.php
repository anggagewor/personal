<?php

namespace Modules\User\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\User\Domain\Contracts\UserRepositoryInterface;
use Modules\User\Domain\Entities\User;
use Modules\User\Infrastructure\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $model = UserModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function save(User $user): User
    {
        $data = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        if ($user->password) {
            $data['password'] = bcrypt($user->password);
        }

        $model = UserModel::updateOrCreate(
            ['id' => $user->id],
            $data,
        );

        return $this->toEntity($model->fresh());
    }

    public function updatePreferences(int $userId, array $preferences): array
    {
        $model = UserModel::findOrFail($userId);
        $current = $model->preferences ?? [];
        $merged = array_merge($current, $preferences);
        $model->preferences = $merged;
        $model->save();

        return $model->getPreferencesWithDefaults();
    }

    public function updateAvatar(int $userId, ?string $path): void
    {
        UserModel::where('id', $userId)->update(['avatar' => $path]);
    }

    public function updatePassword(int $userId, string $hashedPassword): void
    {
        UserModel::where('id', $userId)->update(['password' => $hashedPassword]);
    }

    private function toEntity(UserModel $model): User
    {
        return new User(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            password: null,
            avatar: $model->avatar,
            preferences: $model->preferences ?? [],
            emailVerifiedAt: $model->email_verified_at ? new DateTimeImmutable($model->email_verified_at->toDateTimeString()) : null,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
