<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\ProductData;
use Modules\Pos\Domain\Entities\Product;

interface ProductRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array;

    public function findById(int $id): ?Product;

    public function findActiveByOutlet(int $outletId): array;

    public function create(int $outletId, ProductData $data): Product;

    public function update(int $id, ProductData $data): Product;

    public function deactivate(int $id): void;

    public function existsByName(int $outletId, int $categoryId, string $name, ?int $excludeId = null): bool;

    public function adjustStock(int $variantId, int $quantity, string $type, string $reason): void;

    public function decrementStock(int $variantId, int $quantity): void;

    public function getStockLevel(int $variantId): int;
}
