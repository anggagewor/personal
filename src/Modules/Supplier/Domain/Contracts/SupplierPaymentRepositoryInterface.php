<?php

namespace Modules\Supplier\Domain\Contracts;

use Modules\Supplier\Application\DTO\SupplierPaymentData;
use Modules\Supplier\Domain\Entities\SupplierPayment;

interface SupplierPaymentRepositoryInterface
{
    /**
     * @return SupplierPayment[]
     */
    public function findByPurchaseOrder(int $purchaseOrderId): array;

    /**
     * @return array{data: SupplierPayment[], total: int, per_page: int, current_page: int}
     */
    public function findBySupplierPaginated(int $supplierId, int $perPage): array;

    public function create(SupplierPaymentData $data): SupplierPayment;

    public function getTotalPaidForPO(int $purchaseOrderId): float;
}
