<?php

namespace Modules\Supplier\Application\Actions\SupplierProduct;

use Modules\Supplier\Domain\Contracts\SupplierProductRepositoryInterface;

class UnlinkProductAction
{
    public function __construct(
        private SupplierProductRepositoryInterface $repository,
    ) {}

    public function execute(int $supplierId, int $productVariantId): void
    {
        $this->repository->unlink($supplierId, $productVariantId);
    }
}
