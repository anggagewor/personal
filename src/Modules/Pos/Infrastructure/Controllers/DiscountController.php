<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Discount\CreateDiscountAction;
use Modules\Pos\Application\Actions\Discount\DeleteDiscountAction;
use Modules\Pos\Application\Actions\Discount\EvaluateDiscountsAction;
use Modules\Pos\Application\Actions\Discount\UpdateDiscountAction;
use Modules\Pos\Application\DTO\DiscountData;
use Modules\Pos\Domain\Contracts\DiscountRepositoryInterface;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Infrastructure\Requests\StoreDiscountRequest;
use Modules\Pos\Infrastructure\Requests\UpdateDiscountRequest;
use Modules\Pos\Infrastructure\Resources\DiscountResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class DiscountController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private DiscountRepositoryInterface $discountRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $discounts = $this->discountRepo->findByOutlet($outletId);

        return response()->json([
            'data' => DiscountResource::collection($discounts),
            'message' => 'Daftar diskon berhasil diambil.',
        ]);
    }

    public function store(StoreDiscountRequest $request, int $outletId, CreateDiscountAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        $discount = $action->execute($outletId, new DiscountData(
            name: $validated['name'],
            type: $validated['type'],
            value: $validated['value'],
            minPurchase: $validated['min_purchase'] ?? null,
            memberOnly: $validated['member_only'] ?? false,
            isActive: $validated['is_active'] ?? true,
            priority: $validated['priority'] ?? 0,
            startsAt: $validated['start_date'] ?? null,
            endsAt: $validated['end_date'] ?? null,
            productId: $validated['product_id'] ?? null,
        ));

        return response()->json([
            'data' => DiscountResource::toArray($discount),
            'message' => 'Diskon berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateDiscountRequest $request, int $id, UpdateDiscountAction $action): JsonResponse
    {
        $discount = $this->discountRepo->findById($id);

        if (! $discount) {
            abort(404, 'Diskon tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $discount->outletId, $request);

        $validated = $request->validated();

        $discount = $action->execute($id, new DiscountData(
            name: $validated['name'],
            type: $validated['type'],
            value: $validated['value'],
            minPurchase: $validated['min_purchase'] ?? null,
            memberOnly: $validated['member_only'] ?? false,
            isActive: $validated['is_active'] ?? true,
            priority: $validated['priority'] ?? 0,
            startsAt: $validated['start_date'] ?? null,
            endsAt: $validated['end_date'] ?? null,
            productId: $validated['product_id'] ?? null,
        ));

        return response()->json([
            'data' => DiscountResource::toArray($discount),
            'message' => 'Diskon berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteDiscountAction $action): JsonResponse
    {
        $discount = $this->discountRepo->findById($id);

        if (! $discount) {
            abort(404, 'Diskon tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $discount->outletId, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Diskon berhasil dihapus.',
        ]);
    }

    public function evaluate(Request $request, EvaluateDiscountsAction $action): JsonResponse
    {
        $validated = $request->validate([
            'outlet_id' => 'required|integer',
            'subtotal' => 'required|numeric|min:0',
            'member_id' => 'nullable|integer',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.subtotal' => 'required_with:items|numeric|min:0',
        ]);

        $this->findOwnedOrFail($this->outletRepo, $validated['outlet_id'], $request);

        $result = $action->execute(
            outletId: $validated['outlet_id'],
            subtotal: (float) $validated['subtotal'],
            memberId: $validated['member_id'] ?? null,
            items: $validated['items'] ?? [],
        );

        return response()->json([
            'data' => [
                'applicable' => DiscountResource::collection($result['applicable']),
                'total_discount' => $result['total_discount'],
            ],
            'message' => 'Evaluasi diskon berhasil.',
        ]);
    }
}
