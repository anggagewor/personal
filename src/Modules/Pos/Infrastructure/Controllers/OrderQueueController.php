<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\QrOrder\AcceptOrderAction;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Infrastructure\Resources\OrderQueueResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class OrderQueueController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private TableRepositoryInterface $tableRepository,
        private OutletRepositoryInterface $outletRepository,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $orders = $this->tableRepository->findPendingOrders($outletId);

        return response()->json([
            'data' => OrderQueueResource::collection($orders),
            'message' => 'Daftar pesanan berhasil diambil.',
        ]);
    }

    public function accept(Request $request, int $id, AcceptOrderAction $action): JsonResponse
    {
        $order = $action->execute($id);

        return response()->json([
            'data' => OrderQueueResource::toArray($order),
            'message' => 'Pesanan berhasil diterima.',
        ]);
    }
}
