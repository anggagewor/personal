<?php

namespace Modules\Pos\Application\Actions\Discount;

use Modules\Pos\Domain\Contracts\DiscountRepositoryInterface;

class DeleteDiscountAction
{
    public function __construct(
        private DiscountRepositoryInterface $discountRepo,
    ) {}

    public function execute(int $id): void
    {
        $this->discountRepo->delete($id);
    }
}
