<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pos\Application\Actions\Outlet\CreateOutletAction;
use Modules\Pos\Application\Actions\Outlet\DeleteOutletAction;
use Modules\Pos\Application\Actions\Outlet\UpdateOutletAction;
use Modules\Pos\Application\DTO\OutletData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Enums\BusinessType;
use Modules\Pos\Domain\Enums\PaymentFlowMode;
use Modules\Pos\Infrastructure\Requests\StoreOutletRequest;
use Modules\Pos\Infrastructure\Requests\UpdateOutletRequest;
use Modules\Pos\Infrastructure\Resources\OutletResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class OutletController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private OutletRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $outlets = $this->repository->findByUser($request->user()->id);

        return response()->json([
            'data' => OutletResource::collection($outlets),
        ]);
    }

    public function store(StoreOutletRequest $request, CreateOutletAction $action): JsonResponse
    {
        $validated = $request->validated();

        $outlet = $action->execute(
            userId: $request->user()->id,
            data: new OutletData(
                name: $validated['name'],
                businessType: BusinessType::from($validated['business_type']),
                paymentFlow: isset($validated['payment_flow'])
                    ? PaymentFlowMode::from($validated['payment_flow'])
                    : PaymentFlowMode::PayFirst,
                address: $validated['address'] ?? null,
                phone: $validated['phone'] ?? null,
                settings: $validated['settings'] ?? null,
            ),
        );

        return response()->json([
            'data' => OutletResource::toArray($outlet),
            'message' => 'Outlet berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateOutletRequest $request, int $id, UpdateOutletAction $action): JsonResponse
    {
        $existing = $this->findOwnedOrFail($this->repository, $id, $request);
        $validated = $request->validated();

        $outlet = $action->execute(
            id: $id,
            data: new OutletData(
                name: $validated['name'] ?? $existing->name,
                businessType: isset($validated['business_type'])
                    ? BusinessType::from($validated['business_type'])
                    : $existing->businessType,
                paymentFlow: isset($validated['payment_flow'])
                    ? PaymentFlowMode::from($validated['payment_flow'])
                    : $existing->paymentFlow,
                address: array_key_exists('address', $validated) ? $validated['address'] : $existing->address,
                phone: array_key_exists('phone', $validated) ? $validated['phone'] : $existing->phone,
                settings: array_key_exists('settings', $validated) ? $validated['settings'] : $existing->settings,
            ),
        );

        return response()->json([
            'data' => OutletResource::toArray($outlet),
            'message' => 'Outlet berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteOutletAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Outlet berhasil dihapus.',
        ]);
    }
}
