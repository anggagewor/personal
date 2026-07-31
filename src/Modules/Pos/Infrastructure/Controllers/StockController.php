<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Catalog\AdjustStockAction;
use Modules\Pos\Application\DTO\StockAdjustmentData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Exceptions\InvalidStockAdjustmentException;
use Modules\Pos\Infrastructure\Requests\StoreStockAdjustmentRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class StockController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private ProductRepositoryInterface $productRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function store(StoreStockAdjustmentRequest $request, int $productId, AdjustStockAction $action): JsonResponse
    {
        $product = $this->productRepo->findById($productId);

        if (!$product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $product->outletId, $request);

        $validated = $request->validated();

        try {
            $action->execute(new StockAdjustmentData(
                productVariantId: (int) $validated['product_variant_id'],
                type: $validated['type'],
                quantity: (int) $validated['quantity'],
                reason: $validated['reason'] ?? null,
            ));
        } catch (InvalidStockAdjustmentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Penyesuaian stok berhasil dicatat.',
        ], 201);
    }

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $products = $this->productRepo->findActiveByOutlet($outletId);

        $stockData = [];
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $stockData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_id' => $variant->id,
                    'variant_name' => $variant->name,
                    'stock_quantity' => $variant->stockQuantity,
                    'track_stock' => $product->trackStock,
                ];
            }
        }

        return response()->json([
            'data' => $stockData,
        ]);
    }
}
