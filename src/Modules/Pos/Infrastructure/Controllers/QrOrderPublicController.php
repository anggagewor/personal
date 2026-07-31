<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Pos\Application\Actions\QrOrder\SubmitOrderAction;
use Modules\Pos\Application\DTO\QrOrderData;
use Modules\Pos\Domain\Contracts\ProductRepositoryInterface;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Infrastructure\Models\TableModel;
use Modules\Pos\Infrastructure\Requests\SubmitQrOrderRequest;
use Modules\Pos\Infrastructure\Resources\OrderQueueResource;
use Modules\Pos\Infrastructure\Resources\ProductResource;

class QrOrderPublicController extends Controller
{
    public function __construct(
        private TableRepositoryInterface $tableRepository,
        private ProductRepositoryInterface $productRepository,
    ) {}

    public function menu(string $token): JsonResponse
    {
        $tableModel = TableModel::where('token', $token)->first();

        if (! $tableModel) {
            abort(404, 'Meja tidak ditemukan atau QR code tidak valid.');
        }

        $products = $this->productRepository->findActiveByOutlet($tableModel->outlet_id);

        return response()->json([
            'data' => [
                'table_name' => $tableModel->name,
                'products' => ProductResource::collection($products),
            ],
            'message' => 'Menu berhasil diambil.',
        ]);
    }

    public function submitOrder(SubmitQrOrderRequest $request, string $token, SubmitOrderAction $action): JsonResponse
    {
        $tableModel = TableModel::where('token', $token)->first();

        if (! $tableModel) {
            abort(404, 'Meja tidak ditemukan atau QR code tidak valid.');
        }

        $validated = $request->validated();

        $items = array_map(fn (array $item) => [
            'productId' => $item['product_id'],
            'variantId' => $item['variant_id'] ?? null,
            'quantity' => $item['quantity'],
            'productName' => $item['name'],
            'variantName' => $item['variant_name'] ?? null,
            'unitPrice' => $item['price'],
        ], $validated['items']);

        $data = new QrOrderData(
            items: $items,
            notes: $validated['notes'] ?? null,
        );

        $order = $action->execute($tableModel->id, $data);

        return response()->json([
            'data' => OrderQueueResource::toArray($order),
            'message' => 'Pesanan berhasil dikirim.',
        ], 201);
    }

    public function orderStatus(string $token, int $id): JsonResponse
    {
        $tableModel = TableModel::where('token', $token)->first();

        if (! $tableModel) {
            abort(404, 'Meja tidak ditemukan atau QR code tidak valid.');
        }

        $order = \Modules\Pos\Infrastructure\Models\OrderQueueModel::where('id', $id)
            ->whereHas('tableSession', function ($query) use ($tableModel) {
                $query->where('table_id', $tableModel->id);
            })
            ->first();

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        return response()->json([
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'items' => $order->items,
                'notes' => $order->notes,
                'created_at' => $order->created_at?->toIso8601String(),
                'updated_at' => $order->updated_at?->toIso8601String(),
            ],
            'message' => 'Status pesanan berhasil diambil.',
        ]);
    }
}
