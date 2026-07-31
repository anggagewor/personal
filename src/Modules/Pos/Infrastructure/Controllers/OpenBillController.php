<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pos\Application\Actions\Transaction\CloseOpenBillAction;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Infrastructure\Requests\CloseOpenBillRequest;
use Modules\Pos\Infrastructure\Resources\TransactionResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class OpenBillController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $outlet = $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $openBills = $this->transactionRepo->findOpenBillsByOutlet($outletId);

        return response()->json([
            'data' => TransactionResource::collection($openBills),
            'message' => 'Daftar open bill berhasil diambil.',
        ]);
    }

    public function close(
        CloseOpenBillRequest $request,
        int $id,
        CloseOpenBillAction $action,
    ): JsonResponse {
        $transaction = $this->transactionRepo->findById($id);

        if (! $transaction) {
            abort(404, 'Open bill tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $transaction->outletId, $request);

        try {
            $validated = $request->validated();

            $closed = $action->execute(
                transactionId: $id,
                paymentMethod: $validated['payment_method'],
                paymentMethodType: $validated['payment_method_type'],
                amountTendered: isset($validated['amount_tendered']) ? (float) $validated['amount_tendered'] : null,
            );

            return response()->json([
                'data' => TransactionResource::toArray($closed),
                'message' => 'Open bill berhasil ditutup.',
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
