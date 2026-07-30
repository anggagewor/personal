<?php

namespace Modules\Converter\Application\Actions;

use Modules\Converter\Domain\Contracts\CustomCategoryRepositoryInterface;

class DeleteCustomCategoryAction
{
    public function __construct(
        private CustomCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $categoryId): void
    {
        $this->repository->delete($categoryId);
    }
}
