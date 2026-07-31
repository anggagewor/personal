<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Voucher\BatchCreateVoucherAction;
use Modules\Pos\Application\Actions\Voucher\CreateVoucherAction;
use Modules\Pos\Application\Actions\Voucher\ValidateVoucherAction;
use Modules\Pos\Application\DTO\VoucherData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\VoucherRepositoryInterface;
use Modules\Pos\Domain\Exceptions\InvalidVoucherException;
use Modules\Pos\Infrastructure\Requests\BatchStoreVoucherRequest;
use Modules\Pos\Infrastructure\Requests\StoreVoucherRequest;
use Modules\Pos\Infrastructure\Resources\VoucherResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class VoucherController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private VoucherRepositoryInterface $voucherRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $result = $this->voucherRepo->findByOutletPaginated(
            outletId: $outletId,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => VoucherResource::collection($result['data']),
            'meta' => $result['meta'],
            'message' => 'Daftar voucher berhasil diambil.',
        ]);
    }

    public function store(StoreVoucherRequest $request, int $outletId, CreateVoucherAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        $voucher = $action->execute($outletId, new VoucherData(
            code: $validated['code'],
            type: $validated['discount_type'],
            value: $validated['discount_value'],
            minPurchase: $validated['min_purchase'] ?? null,
            usageLimit: $validated['usage_limit'] ?? null,
            expiresAt: $validated['expires_at'] ?? null,
            isActive: $validated['is_active'] ?? true,
            productId: $validated['product_id'] ?? null,
        ));

        return response()->json([
            'data' => VoucherResource::toArray($voucher),
            'message' => 'Voucher berhasil dibuat.',
        ], 201);
    }

    public function batchStore(BatchStoreVoucherRequest $request, int $outletId, BatchCreateVoucherAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        $templateData = new VoucherData(
            code: '', // Will be generated
            type: $validated['discount_type'],
            value: $validated['discount_value'],
            minPurchase: $validated['min_purchase'] ?? null,
            usageLimit: $validated['usage_limit'] ?? null,
            expiresAt: $validated['expires_at'] ?? null,
            isActive: $validated['is_active'] ?? true,
        );

        $vouchers = $action->execute(
            outletId: $outletId,
            prefix: $validated['prefix'],
            count: $validated['count'],
            templateData: $templateData,
        );

        return response()->json([
            'data' => VoucherResource::collection($vouchers),
            'message' => "Berhasil membuat {$validated['count']} voucher.",
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $voucher = $this->voucherRepo->findById($id);

        if (! $voucher) {
            abort(404, 'Voucher tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $voucher->outletId, $request);

        return response()->json([
            'data' => VoucherResource::toArray($voucher),
        ]);
    }

    public function validate(Request $request, ValidateVoucherAction $action): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer',
            'items.*.subtotal' => 'required_with:items|numeric|min:0',
        ]);

        try {
            $voucher = $action->execute(
                code: $validated['code'],
                subtotal: (float) $validated['subtotal'],
                items: $validated['items'] ?? [],
            );

            // Calculate discount amount based on product scope
            $discountBase = (float) $validated['subtotal'];
            if ($voucher->productId !== null && !empty($validated['items'])) {
                $discountBase = 0.0;
                foreach ($validated['items'] as $item) {
                    if (($item['product_id'] ?? 0) === $voucher->productId) {
                        $discountBase += (float) ($item['subtotal'] ?? 0);
                    }
                }
            }

            $discountAmount = match ($voucher->type->value) {
                'percentage' => $discountBase * ($voucher->value / 100),
                'fixed' => min($voucher->value, $discountBase),
                default => 0.0,
            };

            return response()->json([
                'data' => [
                    'valid' => true,
                    'voucher' => VoucherResource::toArray($voucher),
                    'discount_amount' => round($discountAmount, 2),
                ],
                'message' => 'Voucher valid.',
            ]);
        } catch (InvalidVoucherException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
