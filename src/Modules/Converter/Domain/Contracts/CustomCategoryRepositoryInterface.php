<?php

namespace Modules\Converter\Domain\Contracts;

use Modules\Converter\Domain\Entities\CustomCategory;

interface CustomCategoryRepositoryInterface
{
    public function findById(int $id): ?CustomCategory;

    public function findByUser(int $userId): array;

    public function save(CustomCategory $category): CustomCategory;

    public function delete(int $id): void;
}
