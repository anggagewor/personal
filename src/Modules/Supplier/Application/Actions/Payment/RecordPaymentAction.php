<?php

namespace Modules\Supplier\Application\Actions\Payment;

use Modules\Supplier\Application\DTO\SupplierPaymentData;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Contracts\SupplierPaymentRepositoryInterface;
use Modules\Supplier\Domain\Entities\SupplierPayment;
use Modules\Supplier\Domain\Enums\PaymentStatus;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use Modules\Supplier\Domain\Exceptions\OverPaymentException;

class RecordPaymentAction
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $poRepository,
        private SupplierPaymentRepositoryInterface $paymentRepository,
    ) {}

    public function execute(SupplierPaymentData $data): SupplierPayment
    {
        $po = $this->poRepository->findById($data->purchaseOrderId);

        // 1. Validate PO is not cancelled
        if ($po->status === PurchaseOrderStatus::Cancelled) {
            throw InvalidPurchaseOrderStateException::notAllowed(
                $po->poNumber, 'payment', $po->status->value
            );
        }

        // 2. Calculate outstanding balance
        $totalPaid = $this->paymentRepository->getTotalPaidForPO($data->purchaseOrderId);
        $outstanding = $po->totalAmount - $totalPaid;

        // 3. Validate no overpayment
        if ($data->amount > $outstanding) {
            throw OverPaymentException::create($po->poNumber, $outstanding, $data->amount);
        }

        // 4. Create payment record
        $payment = $this->paymentRepository->create($data);

        // 5. Determine and update payment status
        $newTotalPaid = $totalPaid + $data->amount;

        if ($newTotalPaid >= $po->totalAmount) {
            $this->poRepository->updatePaymentStatus($data->purchaseOrderId, PaymentStatus::Paid);
        } elseif ($newTotalPaid > 0) {
            $this->poRepository->updatePaymentStatus($data->purchaseOrderId, PaymentStatus::Partial);
        }

        return $payment;
    }
}
