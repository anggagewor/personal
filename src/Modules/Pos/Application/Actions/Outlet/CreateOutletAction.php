<?php

namespace Modules\Pos\Application\Actions\Outlet;

use Modules\Pos\Application\DTO\OutletData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Entities\Outlet;

class CreateOutletAction
{
    public function __construct(
        private OutletRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, OutletData $data): Outlet
    {
        return $this->repository->create($userId, $data);
    }
}
