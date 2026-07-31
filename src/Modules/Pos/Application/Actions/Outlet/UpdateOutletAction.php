<?php

namespace Modules\Pos\Application\Actions\Outlet;

use Modules\Pos\Application\DTO\OutletData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Entities\Outlet;

class UpdateOutletAction
{
    public function __construct(
        private OutletRepositoryInterface $repository,
    ) {}

    public function execute(int $id, OutletData $data): Outlet
    {
        return $this->repository->update($id, $data);
    }
}
