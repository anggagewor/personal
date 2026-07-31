<?php

namespace Modules\Supplier\Application\Actions\PurchaseOrder;

use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;

class CancelPurchaseOrderAction
{
    public function __construct(private PurchaseOrderRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $po = $this->repository->findById($id);

        if (!$po->isCancellable()) {
            throw InvalidPurchaseOrderStateException::cannotTransition(
                $po->poNumber, $po->status->value, 'cancelled'
            );
        }

        $this->repository->updateStatus($id, PurchaseOrderStatus::Cancelled);
    }
}
