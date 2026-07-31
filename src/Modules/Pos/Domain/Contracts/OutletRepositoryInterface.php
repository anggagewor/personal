<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\OutletData;
use Modules\Pos\Domain\Entities\Outlet;

interface OutletRepositoryInterface
{
    public function findById(int $id): ?Outlet;

    public function findByUser(int $userId): array;

    public function create(int $userId, OutletData $data): Outlet;

    public function update(int $id, OutletData $data): Outlet;

    public function softDelete(int $id): void;
}
