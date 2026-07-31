<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Application\DTO\CategoryData;
use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Pos\Domain\Entities\Category;
use Modules\Pos\Domain\Exceptions\DuplicateCategoryException;

class UpdateCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function execute(int $id, CategoryData $data): Category
    {
        $category = $this->categoryRepo->findById($id);

        if ($this->categoryRepo->existsByName($category->outletId, $data->name, $id)) {
            throw DuplicateCategoryException::create($data->name);
        }

        return $this->categoryRepo->update($id, $data);
    }
}
