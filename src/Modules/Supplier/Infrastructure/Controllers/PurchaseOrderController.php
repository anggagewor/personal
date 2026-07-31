<?php

namespace Modules\Supplier\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Supplier\Application\Actions\PurchaseOrder\CancelPurchaseOrderAction;
use Modules\Supplier\Application\Actions\PurchaseOrder\ConfirmPurchaseOrderAction;
use Modules\Supplier\Application\Actions\PurchaseOrder\CreatePurchaseOrderAction;
use Modules\Supplier\Application\Actions\PurchaseOrder\UpdatePurchaseOrderAction;
use Modules\Supplier\Application\DTO\PurchaseOrderData;
use Modules\Supplier\Application\DTO\PurchaseOrderItemData;
use Modules\Supplier\Domain\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Supplier\Domain\Contracts\PurchaseOrderRepositoryInterface;
use Modules\Supplier\Domain\Contracts\SupplierPaymentRepositoryInterface;
use Modules\Supplier\Domain\Exceptions\EmptyPurchaseOrderException;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use Modules\Supplier\Infrastructure\Requests\StorePurchaseOrderRequest;
use Modules\Supplier\Infrastructure\Requests\UpdatePurchaseOrderRequest;
use Modules\Supplier\Infrastructure\Resources\PurchaseOrderListResource;
use Modules\Supplier\Infrastructure\Resources\PurchaseOrderResource;

class PurchaseOrderController extends BaseController
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $poRepository,
        private GoodsReceiptRepositoryInterface $receiptRepository,
        private SupplierPaymentRepositoryInterface $paymentRepository,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $filters = array_filter([
            'status' => $request->query('status'),
            'payment_status' => $request->query('payment_status'),
            'supplier_id' => $request->query('supplier_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ], fn ($v) => $v !== null);

        $perPage = (int) $request->query('per_page', 15);

        $result = $this->poRepository->findByOutletPaginated($outletId, $filters, $perPage);

        return response()->json([
            'data' => PurchaseOrderListResource::collection($result['data']),
            'meta' => $result['meta'] ?? [
                'total' => $result['total'] ?? 0,
                'per_page' => $result['per_page'] ?? $perPage,
                'current_page' => $result['current_page'] ?? 1,
            ],
        ]);
    }

    public function store(StorePurchaseOrderRequest $request, int $outletId, CreatePurchaseOrderAction $action): JsonResponse
    {
        $items = collect($request->validated('items'))->map(fn ($item) => new PurchaseOrderItemData(
            productVariantId: $item['product_variant_id'],
            productName: $item['product_name'],
            variantName: $item['variant_name'],
            quantity: $item['quantity'],
            unitCost: $item['unit_cost'],
        ))->all();

        $data = new PurchaseOrderData(
            supplierId: $request->validated('supplier_id'),
            orderDate: $request->validated('order_date'),
            expectedDeliveryDate: $request->validated('expected_delivery_date'),
            notes: $request->validated('notes'),
            items: $items,
        );

        $po = $action->execute($outletId, $data);

        return response()->json([
            'data' => new PurchaseOrderResource($po),
            'message' => 'Purchase order berhasil dibuat.',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $po = $this->poRepository->findById($id);

        if (!$po) {
            abort(404, 'Purchase order tidak ditemukan.');
        }

        $totalPaid = $this->poRepository->getTotalPaid($id);
        $po->totalPaid = $totalPaid;
        $po->outstandingBalance = $po->totalAmount - $totalPaid;

        $receipts = $this->receiptRepository->findByPurchaseOrder($id);
        $payments = $this->paymentRepository->findByPurchaseOrder($id);

        return response()->json([
            'data' => new PurchaseOrderResource($po),
            'receipts' => $receipts,
            'payments' => $payments,
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, int $id, UpdatePurchaseOrderAction $action): JsonResponse
    {
        $items = collect($request->validated('items'))->map(fn ($item) => new PurchaseOrderItemData(
            productVariantId: $item['product_variant_id'],
            productName: $item['product_name'],
            variantName: $item['variant_name'],
            quantity: $item['quantity'],
            unitCost: $item['unit_cost'],
        ))->all();

        $data = new PurchaseOrderData(
            supplierId: $request->validated('supplier_id'),
            orderDate: $request->validated('order_date'),
            expectedDeliveryDate: $request->validated('expected_delivery_date'),
            notes: $request->validated('notes'),
            items: $items,
        );

        try {
            $po = $action->execute($id, $data);
        } catch (InvalidPurchaseOrderStateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new PurchaseOrderResource($po),
            'message' => 'Purchase order berhasil diperbarui.',
        ]);
    }

    public function confirm(int $id, ConfirmPurchaseOrderAction $action): JsonResponse
    {
        try {
            $action->execute($id);
        } catch (InvalidPurchaseOrderStateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (EmptyPurchaseOrderException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Purchase order berhasil dikonfirmasi.',
        ]);
    }

    public function cancel(int $id, CancelPurchaseOrderAction $action): JsonResponse
    {
        try {
            $action->execute($id);
        } catch (InvalidPurchaseOrderStateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Purchase order berhasil dibatalkan.',
        ]);
    }
}
