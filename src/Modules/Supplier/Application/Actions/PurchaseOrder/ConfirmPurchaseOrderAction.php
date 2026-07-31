<?php

namespace Modules\Supplier\Application\Actions\PurchaseOrder;

use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Enums\PaymentStatus;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Domain\Exceptions\EmptyPurchaseOrderException;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;

class ConfirmPurchaseOrderAction
{
    public function __construct(private PurchaseOrderRepositoryInterface $repository) {}

    public function execute(int $id): void
    {
        $po = $this->repository->findById($id);

        // Must be in draft status
        if (!$po->isDraft()) {
            throw InvalidPurchaseOrderStateException::cannotTransition(
                $po->poNumber, $po->status->value, 'confirmed'
            );
        }

        // Must have at least one line item
        if (empty($po->items)) {
            throw EmptyPurchaseOrderException::create($po->poNumber);
        }

        $this->repository->updateStatus($id, PurchaseOrderStatus::Confirmed);
        $this->repository->updatePaymentStatus($id, PaymentStatus::Unpaid);
    }
}
