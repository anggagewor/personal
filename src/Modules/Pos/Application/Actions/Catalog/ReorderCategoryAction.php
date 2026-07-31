<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;

class ReorderCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
    ) {}

    /**
     * @param int[] $orderedIds Category IDs in desired order
     */
    public function execute(array $orderedIds): void
    {
        $this->categoryRepo->reorder($orderedIds);
    }
}
