<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Shift\CloseShiftAction;
use Modules\Pos\Application\Actions\Shift\OpenShiftAction;
use Modules\Pos\Application\DTO\CloseShiftData;
use Modules\Pos\Application\DTO\OpenShiftData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\ShiftRepositoryInterface;
use Modules\Pos\Domain\Exceptions\ShiftException;
use Modules\Pos\Infrastructure\Resources\ShiftResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class ShiftController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private ShiftRepositoryInterface $shiftRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    /**
     * List shifts for an outlet (paginated).
     */
    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $filters = $request->only(['status', 'date_from', 'date_to']);
        $perPage = (int) $request->query('per_page', 15);

        $result = $this->shiftRepo->findByOutletPaginated($outletId, $filters, $perPage);

        return response()->json([
            'data' => ShiftResource::collection($result['data']),
            'meta' => $result['meta'],
            'message' => 'Daftar shift berhasil diambil.',
        ]);
    }

    /**
     * Get the currently active shift for an outlet.
     */
    public function active(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $shift = $this->shiftRepo->findActiveByOutlet($outletId);

        if (!$shift) {
            return response()->json([
                'data' => null,
                'message' => 'Tidak ada shift aktif.',
            ]);
        }

        return response()->json([
            'data' => ShiftResource::toArray($shift),
        ]);
    }

    /**
     * Open a new shift.
     */
    public function open(Request $request, int $outletId, OpenShiftAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $user = $request->user();

        try {
            $shift = $action->execute(new OpenShiftData(
                outletId: $outletId,
                userId: $user->id,
                cashierName: $user->name,
                openingAmount: (float) $validated['opening_amount'],
            ));

            return response()->json([
                'data' => ShiftResource::toArray($shift),
                'message' => 'Shift berhasil dibuka.',
            ], 201);
        } catch (ShiftException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Close an active shift.
     */
    public function close(Request $request, int $id, CloseShiftAction $action): JsonResponse
    {
        $shift = $this->shiftRepo->findById($id);

        if (!$shift) {
            abort(404, 'Shift tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $shift->outletId, $request);

        $validated = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $closed = $action->execute(new CloseShiftData(
                shiftId: $id,
                closingAmount: (float) $validated['closing_amount'],
                notes: $validated['notes'] ?? null,
            ));

            return response()->json([
                'data' => ShiftResource::toArray($closed),
                'message' => 'Shift berhasil ditutup.',
            ]);
        } catch (ShiftException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get shift detail with summary.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $shift = $this->shiftRepo->findById($id);

        if (!$shift) {
            abort(404, 'Shift tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $shift->outletId, $request);

        $cashSales = $this->shiftRepo->getCashSalesDuringShift($id);
        $cashRefunds = $this->shiftRepo->getCashRefundsDuringShift($id);

        return response()->json([
            'data' => array_merge(ShiftResource::toArray($shift), [
                'summary' => [
                    'cash_sales' => $cashSales,
                    'cash_refunds' => $cashRefunds,
                    'net_cash' => $cashSales - $cashRefunds,
                ],
            ]),
        ]);
    }
}
