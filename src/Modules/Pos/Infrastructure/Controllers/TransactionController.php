<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Transaction\CreateTransactionAction;
use Modules\Pos\Application\Actions\Transaction\VoidTransactionAction;
use Modules\Pos\Application\Actions\Voucher\RedeemVoucherAction;
use Modules\Pos\Application\DTO\CheckoutData;
use Modules\Pos\Application\DTO\LineItemData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Exceptions\InsufficientStockException;
use Modules\Pos\Domain\Exceptions\InvalidVoucherException;
use Modules\Pos\Domain\Exceptions\VoidNotAllowedException;
use Modules\Pos\Infrastructure\Requests\StoreTransactionRequest;
use Modules\Pos\Infrastructure\Requests\VoidTransactionRequest;
use Modules\Pos\Infrastructure\Resources\TransactionResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class TransactionController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private TransactionRepositoryInterface $transactionRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $outlet = $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $filters = $request->only(['status', 'payment_method_type', 'date_from', 'date_to', 'member_id']);
        $perPage = (int) $request->query('per_page', 15);

        $result = $this->transactionRepo->findByOutletPaginated($outletId, $filters, $perPage);

        return response()->json([
            'data' => TransactionResource::collection($result['data']),
            'meta' => $result['meta'] ?? null,
            'message' => 'Daftar transaksi berhasil diambil.',
        ]);
    }

    public function store(
        StoreTransactionRequest $request,
        int $outletId,
        CreateTransactionAction $createAction,
        RedeemVoucherAction $redeemVoucherAction,
    ): JsonResponse {
        $outlet = $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        $items = array_map(
            fn (array $item) => new LineItemData(
                productId: $item['product_id'],
                productVariantId: $item['product_variant_id'] ?? null,
                quantity: $item['quantity'],
                unitPrice: $item['unit_price'],
                productName: $item['product_name'],
                variantName: $item['variant_name'] ?? null,
            ),
            $validated['items'],
        );

        $checkoutData = new CheckoutData(
            outletId: $outletId,
            items: $items,
            paymentMethod: $validated['payment_method'] ?? null,
            paymentMethodType: $validated['payment_method_type'] ?? null,
            amountTendered: isset($validated['amount_tendered']) ? (float) $validated['amount_tendered'] : null,
            memberId: $validated['member_id'] ?? null,
            voucherCode: $validated['voucher_code'] ?? null,
            notes: $validated['notes'] ?? null,
        );

        try {
            $transaction = $createAction->execute($checkoutData);

            // Redeem voucher if voucher_code is present
            if (! empty($validated['voucher_code']) && $transaction->id) {
                try {
                    $redeemVoucherAction->execute(
                        $validated['voucher_code'],
                        $transaction->id,
                        $transaction->subtotal,
                    );
                } catch (InvalidVoucherException $e) {
                    // Voucher redemption failure is non-blocking; transaction still created
                }
            }

            return response()->json([
                'data' => TransactionResource::toArray($transaction),
                'message' => 'Transaksi berhasil dibuat.',
            ], 201);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $transaction = $this->transactionRepo->findById($id);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $transaction->outletId, $request);

        return response()->json([
            'data' => TransactionResource::toArray($transaction),
        ]);
    }

    public function void(
        VoidTransactionRequest $request,
        int $id,
        VoidTransactionAction $action,
    ): JsonResponse {
        $transaction = $this->transactionRepo->findById($id);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $transaction->outletId, $request);

        try {
            $voided = $action->execute($id, $request->validated('reason'));

            return response()->json([
                'data' => TransactionResource::toArray($voided),
                'message' => 'Transaksi berhasil di-void.',
            ]);
        } catch (VoidNotAllowedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
