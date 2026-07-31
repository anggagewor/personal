<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Application\DTO\CategoryData;
use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Pos\Domain\Entities\Category;
use Modules\Pos\Domain\Exceptions\DuplicateCategoryException;

class CreateCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function execute(int $outletId, CategoryData $data): Category
    {
        if ($this->categoryRepo->existsByName($outletId, $data->name)) {
            throw DuplicateCategoryException::create($data->name);
        }

        return $this->categoryRepo->create($outletId, $data);
    }
}
