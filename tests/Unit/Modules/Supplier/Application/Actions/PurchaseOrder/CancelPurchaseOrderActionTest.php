<?php

namespace Tests\Unit\Modules\Supplier\Application\Actions\PurchaseOrder;

use Modules\Supplier\Application\Actions\PurchaseOrder\CancelPurchaseOrderAction;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Entities\PurchaseOrder;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use PHPUnit\Framework\TestCase;

class CancelPurchaseOrderActionTest extends TestCase
{
    private PurchaseOrderRepositoryInterface $repository;
    private CancelPurchaseOrderAction $action;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PurchaseOrderRepositoryInterface::class);
        $this->action = new CancelPurchaseOrderAction($this->repository);
    }

    public function test_cancels_draft_po(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Draft,
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->repository->expects($this->once())
            ->method('updateStatus')
            ->with(1, PurchaseOrderStatus::Cancelled);

        $this->action->execute(1);
    }

    public function test_cancels_confirmed_po(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Confirmed,
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->repository->expects($this->once())
            ->method('updateStatus')
            ->with(1, PurchaseOrderStatus::Cancelled);

        $this->action->execute(1);
    }

    public function test_throws_exception_when_po_is_partial(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Partial,
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->expectException(InvalidPurchaseOrderStateException::class);

        $this->action->execute(1);
    }

    public function test_throws_exception_when_po_is_received(): void
    {
        $po = new PurchaseOrder(
            id: 1,
            outletId: 1,
            supplierId: 1,
            poNumber: 'PO-001',
            orderDate: '2024-01-01',
            status: PurchaseOrderStatus::Received,
        );

        $this->repository->method('findById')->with(1)->willReturn($po);

        $this->expectException(InvalidPurchaseOrderStateException::class);

        $this->action->execute(1);
    }
}
