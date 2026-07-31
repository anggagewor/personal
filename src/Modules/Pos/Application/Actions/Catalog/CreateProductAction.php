<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Application\DTO\ProductData;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Entities\Product;
use Modules\Pos\Domain\Exceptions\DuplicateProductException;

class CreateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(int $outletId, ProductData $data): Product
    {
        if ($this->productRepo->existsByName($outletId, $data->categoryId, $data->name)) {
            throw DuplicateProductException::create($data->name);
        }

        // Repository handles SKU auto-generation and default variant creation
        return $this->productRepo->create($outletId, $data);
    }
}
