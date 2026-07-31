<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;

class DeactivateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(int $id): void
    {
        // Force deactivation regardless of errors (Requirement 3.8)
        try {
            $this->productRepo->deactivate($id);
        } catch (\Throwable) {
            // Force exclusion from POS transaction interface even on error
            $this->productRepo->deactivate($id);
        }
    }
}
