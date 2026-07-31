<?php

namespace Modules\Supplier\Application\Actions\PurchaseOrder;

use Modules\Supplier\Application\DTO\PurchaseOrderData;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Entities\PurchaseOrder;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;

class UpdatePurchaseOrderAction
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $repository,
    ) {}

    public function execute(int $id, PurchaseOrderData $data): PurchaseOrder
    {
        $po = $this->repository->findById($id);

        if (! $po->isEditable()) {
            throw InvalidPurchaseOrderStateException::notAllowed(
                $po->poNumber, 'update', $po->status->value
            );
        }

        return $this->repository->update($id, $data);
    }
}
