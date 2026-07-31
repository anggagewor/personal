<?php

namespace Modules\Supplier\Domain\Contracts;

use Modules\Supplier\Application\DTO\PurchaseOrderData;
use Modules\Supplier\Domain\Entities\PurchaseOrder;
use Modules\Supplier\Domain\Enums\PaymentStatus;
use Modules\Supplier\Domain\Enums\PurchaseOrderStatus;

interface PurchaseOrderRepositoryInterface
{
    /**
     * @param array{search?: string, status?: string, supplier_id?: int, date_from?: string, date_to?: string} $filters
     * @return array{data: PurchaseOrder[], total: int, per_page: int, current_page: int}
     */
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array;

    public function findById(int $id): ?PurchaseOrder;

    public function create(int $outletId, PurchaseOrderData $data): PurchaseOrder;

    public function update(int $id, PurchaseOrderData $data): PurchaseOrder;

    public function updateStatus(int $id, PurchaseOrderStatus $status): void;

    public function updatePaymentStatus(int $id, PaymentStatus $status): void;

    public function generatePoNumber(int $outletId): string;

    /**
     * @param array{status?: string, date_from?: string, date_to?: string} $filters
     * @return array{data: PurchaseOrder[], total: int, per_page: int, current_page: int}
     */
    public function findBySupplier(int $supplierId, array $filters, int $perPage): array;

    public function getOutstandingBySupplier(int $supplierId): float;

    public function getTotalPaid(int $purchaseOrderId): float;
}
