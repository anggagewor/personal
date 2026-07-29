<?php

namespace Modules\GoogleDrive\Application\Actions;

use DateTimeImmutable;
use Modules\GoogleDrive\Application\DTO\DriveConnectionData;
use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;
use Modules\GoogleDrive\Domain\Entities\DriveConnection;

class ConnectDriveAction
{
    public function __construct(
        private DriveConnectionRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, DriveConnectionData $data): DriveConnection
    {
        $expiresAt = $data->expiresIn
            ? new DateTimeImmutable("+{$data->expiresIn} seconds")
            : null;

        $connection = new DriveConnection(
            id: null,
            userId: $userId,
            accessToken: $data->accessToken,
            refreshToken: $data->refreshToken,
            email: $data->email,
            tokenExpiresAt: $expiresAt,
        );

        return $this->repository->save($connection);
    }
}
