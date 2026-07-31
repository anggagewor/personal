<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Application\DTO\ProductData;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Entities\Product;
use Modules\Pos\Domain\Entities\ProductVariant;
use Modules\Pos\Domain\Enums\ProductStatus;
use Modules\Pos\Infrastructure\Models\ProductModel;
use Modules\Pos\Infrastructure\Models\ProductVariantModel;
use Modules\Pos\Infrastructure\Models\StockAdjustmentModel;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array
    {
        $query = ProductModel::where('outlet_id', $outletId)
            ->with('variants');

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage);

        return [
            'data' => collect($paginator->items())
                ->map(fn (ProductModel $model) => $this->toEntity($model))
                ->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function findById(int $id): ?Product
    {
        $model = ProductModel::with('variants')->find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findActiveByOutlet(int $outletId): array
    {
        return ProductModel::where('outlet_id', $outletId)
            ->active()
            ->with('variants')
            ->orderBy('name')
            ->get()
            ->map(fn (ProductModel $model) => $this->toEntity($model))
            ->all();
    }

    public function create(int $outletId, ProductData $data): Product
    {
        $sku = $data->sku ?? $this->generateSku($outletId, $data->categoryId);

        $model = ProductModel::create([
            'outlet_id' => $outletId,
            'category_id' => $data->categoryId,
            'name' => $data->name,
            'base_price' => $data->basePrice,
            'sku' => $sku,
            'image' => $data->image,
            'has_variants' => $data->hasVariants,
            'track_stock' => $data->trackStock,
            'status' => ProductStatus::Active->value,
        ]);

        if ($data->hasVariants && !empty($data->variants)) {
            foreach ($data->variants as $variantData) {
                $model->variants()->create([
                    'name' => $variantData->name,
                    'sku' => $variantData->sku,
                    'price' => $variantData->price,
                    'stock_quantity' => $variantData->stockQuantity,
                ]);
            }
        } else {
            // Create a default variant for products without variants
            $model->variants()->create([
                'name' => 'default',
                'sku' => $sku,
                'price' => $data->basePrice,
                'stock_quantity' => 0,
            ]);
        }

        return $this->toEntity($model->fresh()->load('variants'));
    }

    public function update(int $id, ProductData $data): Product
    {
        $model = ProductModel::findOrFail($id);

        $model->update([
            'category_id' => $data->categoryId,
            'name' => $data->name,
            'base_price' => $data->basePrice,
            'sku' => $data->sku ?? $model->sku,
            'image' => $data->image,
            'has_variants' => $data->hasVariants,
            'track_stock' => $data->trackStock,
        ]);

        if ($data->hasVariants && !empty($data->variants)) {
            // Remove existing variants and recreate
            $model->variants()->delete();

            foreach ($data->variants as $variantData) {
                $model->variants()->create([
                    'name' => $variantData->name,
                    'sku' => $variantData->sku,
                    'price' => $variantData->price,
                    'stock_quantity' => $variantData->stockQuantity,
                ]);
            }
        } elseif (!$data->hasVariants) {
            // Ensure a single default variant exists
            $defaultVariant = $model->variants()->where('name', 'default')->first();

            if ($defaultVariant) {
                $defaultVariant->update([
                    'price' => $data->basePrice,
                ]);
            } else {
                $model->variants()->delete();
                $model->variants()->create([
                    'name' => 'default',
                    'sku' => $model->sku,
                    'price' => $data->basePrice,
                    'stock_quantity' => 0,
                ]);
            }
        }

        return $this->toEntity($model->fresh()->load('variants'));
    }

    public function deactivate(int $id): void
    {
        ProductModel::where('id', $id)->update([
            'status' => ProductStatus::Inactive->value,
        ]);
    }

    public function existsByName(int $outletId, int $categoryId, string $name, ?int $excludeId = null): bool
    {
        $query = ProductModel::where('outlet_id', $outletId)
            ->where('category_id', $categoryId)
            ->where('name', $name);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function adjustStock(int $variantId, int $quantity, string $type, string $reason): void
    {
        $variant = ProductVariantModel::findOrFail($variantId);

        // Log the stock adjustment
        StockAdjustmentModel::create([
            'product_variant_id' => $variantId,
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $reason,
            'created_at' => now(),
        ]);

        // Update stock level based on type
        if (in_array($type, ['restock', 'void'])) {
            $variant->increment('stock_quantity', $quantity);
        } else {
            $variant->decrement('stock_quantity', $quantity);
        }
    }

    public function decrementStock(int $variantId, int $quantity): void
    {
        ProductVariantModel::where('id', $variantId)
            ->decrement('stock_quantity', $quantity);
    }

    public function getStockLevel(int $variantId): int
    {
        return (int) ProductVariantModel::where('id', $variantId)
            ->value('stock_quantity');
    }

    private function generateSku(int $outletId, int $categoryId): string
    {
        $category = \Modules\Pos\Infrastructure\Models\CategoryModel::find($categoryId);
        $initials = $category
            ? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category->name), 0, 3))
            : 'GEN';

        $sequential = ProductModel::where('outlet_id', $outletId)
            ->where('category_id', $categoryId)
            ->count() + 1;

        return sprintf('%d-%s-%04d', $outletId, $initials, $sequential);
    }

    private function toEntity(ProductModel $model): Product
    {
        $variants = $model->relationLoaded('variants')
            ? $model->variants->map(fn (ProductVariantModel $v) => $this->toVariantEntity($v))->all()
            : [];

        return new Product(
            id: $model->id,
            outletId: $model->outlet_id,
            categoryId: $model->category_id,
            name: $model->name,
            basePrice: (float) $model->base_price,
            status: ProductStatus::from($model->status),
            sku: $model->sku,
            image: $model->image,
            hasVariants: $model->has_variants,
            trackStock: $model->track_stock,
            variants: $variants,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
            updatedAt: $model->updated_at
                ? new DateTimeImmutable($model->updated_at->toDateTimeString())
                : null,
        );
    }

    private function toVariantEntity(ProductVariantModel $model): ProductVariant
    {
        return new ProductVariant(
            id: $model->id,
            productId: $model->product_id,
            name: $model->name,
            sku: $model->sku,
            price: (float) $model->price,
            stockQuantity: $model->stock_quantity,
            createdAt: $model->created_at
                ? new DateTimeImmutable($model->created_at->toDateTimeString())
                : null,
            updatedAt: $model->updated_at
                ? new DateTimeImmutable($model->updated_at->toDateTimeString())
                : null,
        );
    }
}
