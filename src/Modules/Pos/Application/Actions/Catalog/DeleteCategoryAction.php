<?php

namespace Modules\Pos\Application\Actions\Catalog;

use Modules\Pos\Application\DTO\CategoryData;
use Modules\Pos\Application\DTO\ProductData;
use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Entities\Category;

class DeleteCategoryAction
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
        private ProductRepositoryInterface $productRepo,
    ) {}

    public function execute(int $id): void
    {
        $category = $this->categoryRepo->findById($id);

        // Find or create "Uncategorized" category for the outlet
        $uncategorized = $this->findOrCreateUncategorized($category->outletId);

        // Reassign all products from deleted category to Uncategorized
        $this->reassignProducts($category->outletId, $id, $uncategorized->id);

        $this->categoryRepo->delete($id);
    }

    private function findOrCreateUncategorized(int $outletId): Category
    {
        $categories = $this->categoryRepo->findByOutlet($outletId);

        foreach ($categories as $category) {
            if ($category->name === 'Uncategorized') {
                return $category;
            }
        }

        return $this->categoryRepo->create($outletId, new CategoryData(
            name: 'Uncategorized',
        ));
    }

    private function reassignProducts(int $outletId, int $fromCategoryId, int $toCategoryId): void
    {
        $products = $this->productRepo->findByOutletPaginated(
            $outletId,
            ['category_id' => $fromCategoryId],
            1000
        );

        $items = $products['data'] ?? $products;

        foreach ($items as $product) {
            $this->productRepo->update($product->id, new ProductData(
                name: $product->name,
                basePrice: $product->basePrice,
                categoryId: $toCategoryId,
                sku: $product->sku,
                image: $product->image,
                hasVariants: $product->hasVariants,
                trackStock: $product->trackStock,
            ));
        }
    }
}
