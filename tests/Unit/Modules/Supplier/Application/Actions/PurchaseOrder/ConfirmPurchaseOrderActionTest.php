<?php

namespace Tests\Unit\Modules\Supplier\Application\Actions\PurchaseOrder;

use Modules\Supplier\Application\Actions\PurchaseOrder\ConfirmPurchaseOrderAction;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Entities\PurchaseOrder;
use Modules\Supplier\Domain\Entities\PurchaseOrderItem;
use Modules\Supplier\Domain\Enums\PaymentStatus;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Domain\Exceptions\EmptyPurchaseOrderException;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use PHPUnit\Framework\TestCase;

class ConfirmPurchaseOrderActionTest extends TestCase
{
    private PurchaseOrderRepositoryInterface $repository;
    private ConfirmPurchaseOrderAction $action;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PurchaseOrderRepositoryInterface::class);
        $this->action = new ConfirmPurchaseOrderAction($this->repository);
    }

    public function test_confirms_draft_po_with_items(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Draft,
            items: [$this->createMock(PurchaseOrderItem::class)],
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->repository->expects($this->once())
            ->method('updateStatus')
            ->with(1, PurchaseOrderStatus::Confirmed);

        $this->repository->expects($this->once())
            ->method('updatePaymentStatus')
            ->with(1, PaymentStatus::Unpaid);

        $this->action->execute(1);
    }

    public function test_throws_exception_when_po_is_not_draft(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Confirmed,
            items: [$this->createMock(PurchaseOrderItem::class)],
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->expectException(InvalidPurchaseOrderStateException::class);

        $this->action->execute(1);
    }

    public function test_throws_exception_when_po_has_no_items(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Draft,
            items: [],
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->expectException(EmptyPurchaseOrderException::class);

        $this->action->execute(1);
    }
}
