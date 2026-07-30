<?php

namespace Modules\Converter\Domain\Contracts;

use Modules\Converter\Domain\Entities\CustomUnit;

interface CustomUnitRepositoryInterface
{
    public function findById(int $id): ?CustomUnit;

    public function findByCategory(int $categoryId): array;

    public function save(CustomUnit $unit): CustomUnit;

    public function delete(int $id): void;
}
