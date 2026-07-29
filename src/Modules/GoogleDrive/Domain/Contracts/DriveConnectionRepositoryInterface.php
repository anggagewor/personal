<?php

namespace Modules\GoogleDrive\Domain\Contracts;

use Modules\GoogleDrive\Domain\Entities\DriveConnection;

interface DriveConnectionRepositoryInterface
{
    public function findByUserId(int $userId): ?DriveConnection;

    public function save(DriveConnection $connection): DriveConnection;

    public function deleteByUserId(int $userId): void;
}
