<?php

namespace Modules\Supplier\Application\Actions\Supplier;

use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;

class DeleteSupplierAction
{
    public function __construct(
        private SupplierRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->softDelete($id);
    }
}
