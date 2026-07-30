<?php

namespace Modules\Converter\Application\Actions;

use Modules\Converter\Application\DTO\CustomCategoryData;
use Modules\Converter\Domain\Contracts\CustomCategoryRepositoryInterface;
use Modules\Converter\Domain\Entities\CustomCategory;

class UpdateCustomCategoryAction
{
    public function __construct(
        private CustomCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $categoryId, CustomCategoryData $data): CustomCategory
    {
        $existing = $this->repository->findById($categoryId);

        $category = new CustomCategory(
            id: $categoryId,
            userId: $existing->userId,
            name: $data->name,
            description: $data->description,
            icon: $data->icon,
        );

        return $this->repository->save($category);
    }
}
