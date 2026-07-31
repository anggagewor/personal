<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Infrastructure\Models\PaymentMethodModel;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class PaymentMethodController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $outlet = $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $methods = PaymentMethodModel::where('outlet_id', $outletId)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $methods->map(fn (PaymentMethodModel $m) => [
                'id' => $m->id,
                'outlet_id' => $m->outlet_id,
                'type' => $m->type,
                'name' => $m->name,
                'is_active' => $m->is_active,
                'settings' => $m->settings,
                'sort_order' => $m->sort_order,
                'created_at' => $m->created_at?->format('Y-m-d\TH:i:s.000000\Z'),
                'updated_at' => $m->updated_at?->format('Y-m-d\TH:i:s.000000\Z'),
            ])->all(),
            'message' => 'Daftar metode pembayaran berhasil diambil.',
        ]);
    }

    public function store(Request $request, int $outletId): JsonResponse
    {
        $outlet = $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:cash,bank_transfer,e_wallet,custom'],
            'name' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $method = PaymentMethodModel::create([
            'outlet_id' => $outletId,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
            'settings' => $validated['settings'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'data' => [
                'id' => $method->id,
                'outlet_id' => $method->outlet_id,
                'type' => $method->type,
                'name' => $method->name,
                'is_active' => $method->is_active,
                'settings' => $method->settings,
                'sort_order' => $method->sort_order,
                'created_at' => $method->created_at?->format('Y-m-d\TH:i:s.000000\Z'),
                'updated_at' => $method->updated_at?->format('Y-m-d\TH:i:s.000000\Z'),
            ],
            'message' => 'Metode pembayaran berhasil ditambahkan.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $method = PaymentMethodModel::find($id);

        if (! $method) {
            abort(404, 'Metode pembayaran tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $method->outlet_id, $request);

        $validated = $request->validate([
            'type' => ['sometimes', 'string', 'in:cash,bank_transfer,e_wallet,custom'],
            'name' => ['sometimes', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $method->update($validated);

        return response()->json([
            'data' => [
                'id' => $method->id,
                'outlet_id' => $method->outlet_id,
                'type' => $method->type,
                'name' => $method->name,
                'is_active' => $method->is_active,
                'settings' => $method->settings,
                'sort_order' => $method->sort_order,
                'created_at' => $method->created_at?->format('Y-m-d\TH:i:s.000000\Z'),
                'updated_at' => $method->updated_at?->format('Y-m-d\TH:i:s.000000\Z'),
            ],
            'message' => 'Metode pembayaran berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $method = PaymentMethodModel::find($id);

        if (! $method) {
            abort(404, 'Metode pembayaran tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $method->outlet_id, $request);

        $method->delete();

        return response()->json([
            'message' => 'Metode pembayaran berhasil dihapus.',
        ]);
    }
}
