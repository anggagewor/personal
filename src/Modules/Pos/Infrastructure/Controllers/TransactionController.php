<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Transaction\CreateTransactionAction;
use Modules\Pos\Application\Actions\Transaction\RefundTransactionAction;
use Modules\Pos\Application\Actions\Transaction\VoidTransactionAction;
use Modules\Pos\Application\Actions\Voucher\RedeemVoucherAction;
use Modules\Pos\Application\DTO\CheckoutData;
use Modules\Pos\Application\DTO\LineItemData;
use Modules\Pos\Application\DTO\RefundData;
use Modules\Pos\Application\DTO\RefundItemData;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Pos\Domain\Exceptions\InsufficientStockException;
use Modules\Pos\Domain\Exceptions\InvalidVoucherException;
use Modules\Pos\Domain\Exceptions\RefundNotAllowedException;
use Modules\Pos\Domain\Exceptions\VoidNotAllowedException;
use Modules\Pos\Infrastructure\Requests\RefundTransactionRequest;
use Modules\Pos\Infrastructure\Requests\StoreTransactionRequest;
use Modules\Pos\Infrastructure\Requests\VoidTransactionRequest;
use Modules\Pos\Infrastructure\Resources\RefundResource;
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

        // Evaluate discounts server-side for integrity
        $discountAmount = 0.0;
        $appliedDiscounts = [];
        $discountIds = $validated['discount_ids'] ?? [];
        if (!empty($discountIds)) {
            $evaluateAction = app(\Modules\Pos\Application\Actions\Discount\EvaluateDiscountsAction::class);
            $evalItems = array_map(fn (array $item) => [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ], $validated['items']);

            $subtotal = array_sum(array_column($evalItems, 'subtotal'));
            $evalResult = $evaluateAction->execute(
                outletId: $outletId,
                subtotal: $subtotal,
                memberId: $validated['member_id'] ?? null,
                items: $evalItems,
            );
            $discountAmount = $evalResult['total_discount'];

            // Build applied discounts detail
            $remaining = $subtotal;
            foreach ($evalResult['applicable'] as $disc) {
                $base = $disc->productId !== null
                    ? collect($evalItems)->where('product_id', $disc->productId)->sum('subtotal')
                    : $remaining;

                $amount = match ($disc->type->value) {
                    'percentage' => $base * ($disc->value / 100),
                    'fixed' => min($disc->value, $base),
                    'buy_x_get_y' => $this->calculateBxgyAmount($disc, $evalItems),
                    default => 0.0,
                };

                if ($amount > 0) {
                    $appliedDiscounts[] = [
                        'discount_id' => $disc->id,
                        'name' => $disc->name,
                        'type' => $disc->type->value,
                        'value' => $disc->value,
                        'amount' => round($amount, 2),
                    ];
                    $remaining -= $amount;
                }
            }
        }

        // Evaluate voucher discount
        $voucherDiscount = 0.0;
        $voucherWarning = null;
        if (!empty($validated['voucher_code'])) {
            $validateAction = app(\Modules\Pos\Application\Actions\Voucher\ValidateVoucherAction::class);
            try {
                $voucher = $validateAction->execute(
                    code: $validated['voucher_code'],
                    subtotal: array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_price'], $validated['items'])) - $discountAmount,
                    items: array_map(fn ($i) => ['product_id' => $i['product_id'], 'subtotal' => $i['quantity'] * $i['unit_price']], $validated['items']),
                );
                $voucherBase = $voucher->productId !== null
                    ? collect($validated['items'])->where('product_id', $voucher->productId)->sum(fn ($i) => $i['quantity'] * $i['unit_price'])
                    : array_sum(array_map(fn ($i) => $i['quantity'] * $i['unit_price'], $validated['items'])) - $discountAmount;

                $voucherDiscount = match ($voucher->type->value) {
                    'percentage' => $voucherBase * ($voucher->value / 100),
                    'fixed' => min($voucher->value, $voucherBase),
                    default => 0.0,
                };

                if ($voucherDiscount > 0) {
                    $appliedDiscounts[] = [
                        'discount_id' => null,
                        'name' => 'Voucher ' . $validated['voucher_code'],
                        'type' => $voucher->type->value,
                        'value' => $voucher->value,
                        'amount' => round($voucherDiscount, 2),
                    ];
                }
            } catch (InvalidVoucherException $e) {
                $voucherWarning = $e->getMessage();
            } catch (\Throwable) {
                $voucherWarning = 'Voucher tidak dapat divalidasi. Transaksi tetap diproses tanpa voucher.';
            }
        }

        $totalDiscountAmount = $discountAmount + $voucherDiscount;

        $checkoutData = new CheckoutData(
            outletId: $outletId,
            items: $items,
            paymentMethod: $validated['payment_method'] ?? null,
            paymentMethodType: $validated['payment_method_type'] ?? null,
            amountTendered: isset($validated['amount_tendered']) ? (float) $validated['amount_tendered'] : null,
            memberId: $validated['member_id'] ?? null,
            voucherCode: $validated['voucher_code'] ?? null,
            notes: $validated['notes'] ?? null,
            discountAmount: $totalDiscountAmount,
            appliedDiscounts: $appliedDiscounts,
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
                'warnings' => array_filter([
                    $voucherWarning ? ['type' => 'voucher_invalid', 'message' => $voucherWarning] : null,
                ]),
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

    /**
     * Calculate Buy X Get Y discount amount for a given discount and items.
     */
    private function calculateBxgyAmount(object $disc, array $items): float
    {
        if ($disc->buyQuantity === null || $disc->getQuantity === null || $disc->productId === null) {
            return 0.0;
        }

        $totalQty = 0;
        $unitPrice = 0.0;
        foreach ($items as $item) {
            if (($item['product_id'] ?? 0) === $disc->productId) {
                $totalQty += (int) ($item['quantity'] ?? 0);
                $itemQty = (int) ($item['quantity'] ?? 1);
                if ($itemQty > 0) {
                    $unitPrice = (float) ($item['subtotal'] ?? 0) / $itemQty;
                }
            }
        }

        $setSize = $disc->buyQuantity + $disc->getQuantity;
        if ($totalQty < $setSize) {
            return 0.0;
        }

        $completeSets = intdiv($totalQty, $setSize);
        $freeItems = $completeSets * $disc->getQuantity;

        return $freeItems * $unitPrice;
    }

    public function refund(
        RefundTransactionRequest $request,
        int $id,
        RefundTransactionAction $action,
    ): JsonResponse {
        $transaction = $this->transactionRepo->findById($id);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $transaction->outletId, $request);

        $validated = $request->validated();

        $items = array_map(
            fn (array $item) => new RefundItemData(
                transactionItemId: $item['transaction_item_id'],
                quantity: $item['quantity'],
            ),
            $validated['items'],
        );

        $refundData = new RefundData(
            transactionId: $id,
            items: $items,
            reason: $validated['reason'],
            refundMethod: $validated['refund_method'] ?? null,
        );

        try {
            $refund = $action->execute($refundData);

            return response()->json([
                'data' => RefundResource::toArray($refund),
                'message' => 'Refund berhasil diproses.',
            ], 201);
        } catch (RefundNotAllowedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function refunds(Request $request, int $id): JsonResponse
    {
        $transaction = $this->transactionRepo->findById($id);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        // Verify ownership via outlet
        $this->findOwnedOrFail($this->outletRepo, $transaction->outletId, $request);

        $refunds = $this->transactionRepo->findRefundsByTransaction($id);

        return response()->json([
            'data' => RefundResource::collection($refunds),
            'message' => 'Daftar refund berhasil diambil.',
        ]);
    }
}
