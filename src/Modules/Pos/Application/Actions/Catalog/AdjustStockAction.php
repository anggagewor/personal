<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Application\DTO\StockAdjustmentData;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Exceptions\InvalidStockAdjustmentException;

class AdjustStockAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(StockAdjustmentData $data): void
    {
        if ($data->quantity === 0) {
            throw InvalidStockAdjustmentException::zeroQuantity();
        }

        $this->productRepo->adjustStock(
            $data->productVariantId,
            $data->quantity,
            $data->type,
            $data->reason ?? '',
        );
    }
}
