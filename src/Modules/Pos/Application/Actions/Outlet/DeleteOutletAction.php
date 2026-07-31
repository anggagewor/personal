<?php

namespace Modules\Pos\Application\Actions\Outlet;

use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;

class DeleteOutletAction
{
    public function __construct(
        private OutletRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->softDelete($id);
    }
}
