<?php

namespace Modules\Supplier\Application\Actions\PurchaseOrder;

use Modules\Supplier\Application\DTO\PurchaseOrderData;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Entities\PurchaseOrder;

class CreatePurchaseOrderAction
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $repository,
    ) {}

    public function execute(int $outletId, PurchaseOrderData $data): PurchaseOrder
    {
        return $this->repository->create($outletId, $data);
    }
}
