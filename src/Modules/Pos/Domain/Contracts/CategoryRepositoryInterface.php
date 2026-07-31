<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\CategoryData;
use Modules\Pos\Domain\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findByOutlet(int $outletId): array;

    public function findById(int $id): ?Category;

    public function create(int $outletId, CategoryData $data): Category;

    public function update(int $id, CategoryData $data): Category;

    public function delete(int $id): void;

    public function reorder(array $orderedIds): void;

    public function existsByName(int $outletId, string $name, ?int $excludeId = null): bool;
}
