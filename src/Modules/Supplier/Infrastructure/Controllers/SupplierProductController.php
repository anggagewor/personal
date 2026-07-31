<?php

namespace Modules\Supplier\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Supplier\Application\Actions\SupplierProduct\LinkProductAction;
use Modules\Supplier\Application\Actions\SupplierProduct\UnlinkProductAction;
use Modules\Supplier\Application\DTO\SupplierProductData;
use Modules\Supplier\Domain\Contracts\SupplierProductRepositoryInterface;
use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Supplier\Infrastructure\Requests\LinkProductRequest;
use Modules\Supplier\Infrastructure\Resources\SupplierProductResource;

class SupplierProductController extends BaseController
{
    public function __construct(
        private SupplierProductRepositoryInterface $supplierProductRepo,
        private SupplierRepositoryInterface $supplierRepo,
    ) {}

    public function index(Request $request, int $id): JsonResponse
    {
        $supplier = $this->supplierRepo->findById($id);

        if (!$supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        $products = $this->supplierProductRepo->findBySupplier($id);

        return response()->json([
            'data' => SupplierProductResource::collection($products),
        ]);
    }

    public function link(LinkProductRequest $request, int $id, LinkProductAction $action): JsonResponse
    {
        $supplier = $this->supplierRepo->findById($id);

        if (!$supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        $data = new SupplierProductData(
            productVariantId: (int) $request->validated('product_variant_id'),
            defaultUnitCost: $request->validated('default_unit_cost'),
        );

        try {
            $product = $action->execute($id, $data);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new SupplierProductResource($product),
            'message' => 'Produk berhasil dihubungkan ke supplier.',
        ], 201);
    }

    public function unlink(Request $request, int $id, int $variantId, UnlinkProductAction $action): JsonResponse
    {
        $supplier = $this->supplierRepo->findById($id);

        if (!$supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        try {
            $action->execute($id, $variantId);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(null, 204);
    }
}
