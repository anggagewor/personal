<?php

namespace Modules\Supplier\Application\Actions\SupplierProduct;

use Modules\Supplier\Application\DTO\SupplierProductData;
use Modules\Supplier\Domain\Contracts\SupplierProductRepositoryInterface;
use Modules\Supplier\Domain\Entities\SupplierProduct;

class LinkProductAction
{
    public function __construct(
        private SupplierProductRepositoryInterface $repository,
    ) {}

    public function execute(int $supplierId, SupplierProductData $data): SupplierProduct
    {
        $existing = $this->repository->findBySupplier($supplierId);
        $alreadyLinked = collect($existing)->contains(
            fn (SupplierProduct $p) => $p->productVariantId === $data->productVariantId
        );

        if ($alreadyLinked) {
            throw new \DomainException('Produk ini sudah terhubung dengan supplier.');
        }

        return $this->repository->link($supplierId, $data);
    }
}
