<?php

namespace Modules\GoogleDrive\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Entities\DriveConnection;
use Modules\GoogleDrive\Infrastructure\Models\DriveConnectionModel;

class EloquentDriveConnectionRepository implements DriveConnectionRepositoryInterface
{
    public function findByUserId(int $userId): ?DriveConnection
    {
        $model = DriveConnectionModel::where('user_id', $userId)->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function save(DriveConnection $connection): DriveConnection
    {
        $model = DriveConnectionModel::updateOrCreate(
            ['user_id' => $connection->userId],
            [
                'access_token' => $connection->accessToken,
                'refresh_token' => $connection->refreshToken,
                'email' => $connection->email,
                'token_expires_at' => $connection->tokenExpiresAt?->format('Y-m-d H:i:s'),
            ]
        );

        return $this->toEntity($model->fresh());
    }

    public function deleteByUserId(int $userId): void
    {
        DriveConnectionModel::where('user_id', $userId)->delete();
    }

    private function toEntity(DriveConnectionModel $model): DriveConnection
    {
        return new DriveConnection(
            id: $model->id,
            userId: $model->user_id,
            accessToken: $model->access_token,
            refreshToken: $model->refresh_token,
            email: $model->email,
            tokenExpiresAt: $model->token_expires_at
                ? new DateTimeImmutable($model->token_expires_at->toDateTimeString())
                : null,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
        );
    }
}
