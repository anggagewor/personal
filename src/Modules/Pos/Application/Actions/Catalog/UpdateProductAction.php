<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Application\DTO\ProductData;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Entities\Product;
use Modules\Pos\Domain\Exceptions\DuplicateProductException;

class UpdateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(int $id, ProductData $data): Product
    {
        $product = $this->productRepo->findById($id);

        if ($this->productRepo->existsByName($product->outletId, $data->categoryId, $data->name, $id)) {
            throw DuplicateProductException::create($data->name);
        }

        return $this->productRepo->update($id, $data);
    }
}
