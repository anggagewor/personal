<?php

namespace Modules\Supplier\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Supplier\Application\Actions\GoodsReceipt\CreateGoodsReceiptAction;
use Modules\Supplier\Application\DTO\GoodsReceiptData;
use Modules\Supplier\Application\DTO\GoodsReceiptItemData;
use Modules\Supplier\Domain\Contracts\GoodsReceiptRepositoryInterface;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use Modules\Supplier\Domain\Exceptions\OverDeliveryException;
use Modules\Supplier\Infrastructure\Requests\StoreGoodsReceiptRequest;
use Modules\Supplier\Infrastructure\Resources\GoodsReceiptResource;

class GoodsReceiptController extends Controller
{
    public function __construct(
        private GoodsReceiptRepositoryInterface $receiptRepository,
    ) {}

    public function index(int $id): JsonResponse
    {
        $receipts = $this->receiptRepository->findByPurchaseOrder($id);

        return response()->json([
            'data' => GoodsReceiptResource::collection($receipts),
        ]);
    }

    public function store(StoreGoodsReceiptRequest $request, int $id, CreateGoodsReceiptAction $action): JsonResponse
    {
        $items = collect($request->validated('items'))->map(fn ($item) => new GoodsReceiptItemData(
            purchaseOrderItemId: $item['purchase_order_item_id'],
            productVariantId: $item['product_variant_id'],
            quantity: $item['quantity'],
        ))->all();

        $data = new GoodsReceiptData(
            receiptDate: $request->validated('receipt_date'),
            notes: $request->validated('notes'),
            items: $items,
        );

        try {
            $receipt = $action->execute($id, $data);
        } catch (OverDeliveryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (InvalidPurchaseOrderStateException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new GoodsReceiptResource($receipt),
            'message' => 'Penerimaan barang berhasil dicatat.',
        ], 201);
    }
}
