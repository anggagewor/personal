<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Catalog\CreateProductAction;
use Modules\Pos\Application\Actions\Catalog\DeactivateProductAction;
use Modules\Pos\Application\Actions\Catalog\UpdateProductAction;
use Modules\Pos\Application\DTO\ProductData;
use Modules\Pos\Application\DTO\ProductVariantData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Exceptions\DuplicateProductException;
use Modules\Pos\Infrastructure\Requests\StoreProductRequest;
use Modules\Pos\Infrastructure\Requests\UpdateProductRequest;
use Modules\Pos\Infrastructure\Resources\ProductResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class ProductController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $filters = array_filter([
            'category_id' => $request->query('category_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
        ], fn ($v) => $v !== null);

        $perPage = (int) $request->query('per_page', 15);

        $result = $this->productRepo->findByOutletPaginated($outletId, $filters, $perPage);

        return response()->json([
            'data' => ProductResource::collection($result['data'] ?? $result),
            'meta' => $result['meta'] ?? null,
        ]);
    }

    public function store(StoreProductRequest $request, int $outletId, CreateProductAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $variants = array_map(
            fn (array $v) => new ProductVariantData(
                name: $v['name'],
                price: (float) $v['price'],
                sku: $v['sku'] ?? null,
                stockQuantity: $v['stock_quantity'] ?? 0,
            ),
            $validated['variants'] ?? [],
        );

        try {
            $product = $action->execute(
                outletId: $outletId,
                data: new ProductData(
                    name: $validated['name'],
                    basePrice: (float) $validated['base_price'],
                    categoryId: (int) $validated['category_id'],
                    sku: $validated['sku'] ?? null,
                    image: $imagePath,
                    hasVariants: $validated['has_variants'] ?? false,
                    trackStock: $validated['track_stock'] ?? true,
                    variants: $variants,
                ),
            );
        } catch (DuplicateProductException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'data' => ProductResource::toArray($product),
            'message' => 'Produk berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = $this->productRepo->findById($id);

        if (!$product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $product->outletId, $request);

        return response()->json([
            'data' => ProductResource::toArray($product),
        ]);
    }

    public function update(UpdateProductRequest $request, int $id, UpdateProductAction $action): JsonResponse
    {
        $product = $this->productRepo->findById($id);

        if (!$product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $product->outletId, $request);

        $validated = $request->validated();

        // Handle image upload / removal
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        } elseif (!empty($validated['remove_image'])) {
            // Remove existing image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = null;
        }

        $variants = isset($validated['variants'])
            ? array_map(
                fn (array $v) => new ProductVariantData(
                    name: $v['name'],
                    price: (float) $v['price'],
                    sku: $v['sku'] ?? null,
                    stockQuantity: $v['stock_quantity'] ?? 0,
                ),
                $validated['variants'],
            )
            : $product->variants;

        // If variants is still an array of entities, convert to DTOs
        if (!empty($variants) && isset($variants[0]) && !($variants[0] instanceof ProductVariantData)) {
            $variants = [];
        }

        try {
            $updated = $action->execute(
                id: $id,
                data: new ProductData(
                    name: $validated['name'] ?? $product->name,
                    basePrice: isset($validated['base_price']) ? (float) $validated['base_price'] : $product->basePrice,
                    categoryId: $validated['category_id'] ?? $product->categoryId,
                    sku: array_key_exists('sku', $validated) ? $validated['sku'] : $product->sku,
                    image: $imagePath,
                    hasVariants: $validated['has_variants'] ?? $product->hasVariants,
                    trackStock: $validated['track_stock'] ?? $product->trackStock,
                    variants: $variants,
                ),
            );
        } catch (DuplicateProductException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'data' => ProductResource::toArray($updated),
            'message' => 'Produk berhasil diperbarui.',
        ]);
    }

    public function deactivate(Request $request, int $id, DeactivateProductAction $action): JsonResponse
    {
        $product = $this->productRepo->findById($id);

        if (!$product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $product->outletId, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Produk berhasil dinonaktifkan.',
        ]);
    }
}
