<?php

namespace Modules\Supplier\Domain\Contracts;

use Modules\Supplier\Application\DTO\SupplierData;
use Modules\Supplier\Domain\Entities\Supplier;

interface SupplierRepositoryInterface
{
    /**
     * @param array{search?: string, status?: string} $filters
     * @return array{data: Supplier[], total: int, per_page: int, current_page: int}
     */
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array;

    public function findById(int $id): ?Supplier;

    public function create(int $outletId, SupplierData $data): Supplier;

    public function update(int $id, SupplierData $data): Supplier;

    public function softDelete(int $id): void;

    /**
     * @return Supplier[]
     */
    public function search(int $outletId, string $query): array;

    public function existsByName(int $outletId, string $name, ?int $excludeId = null): bool;

    public function getTotalDebt(int $supplierId): float;
}
