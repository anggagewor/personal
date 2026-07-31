<?php

namespace Modules\Pos\Application\Actions\Discount;

use Modules\Pos\Application\DTO\DiscountData;
use Modules\Pos\Domain\Contracts\DiscountRepositoryInterface;
use Modules\Pos\Domain\Entities\Discount;

class CreateDiscountAction
{
    public function __construct(
        private DiscountRepositoryInterface $discountRepo,
    ) {}

    public function execute(int $outletId, DiscountData $data): Discount
    {
        return $this->discountRepo->create($outletId, $data);
    }
}
