<?php

namespace Modules\GoogleDrive\Application\Actions;

use Modules\GoogleDrive\Domain\Contracts\DriveConnectionRepositoryInterface;

class DisconnectDriveAction
{
    public function __construct(
        private DriveConnectionRepositoryInterface $repository,
    ) {}

    public function execute(int $userId): void
    {
        $this->repository->deleteByUserId($userId);
    }
}
