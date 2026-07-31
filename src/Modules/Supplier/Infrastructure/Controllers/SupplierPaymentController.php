<?php

namespace Modules\Supplier\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Supplier\Application\Actions\Payment\RecordPaymentAction;
use Modules\Supplier\Application\DTO\SupplierPaymentData;
use Modules\Supplier\Domain\Contracts\SupplierPaymentRepositoryInterface;
use Modules\Supplier\Domain\Enums\PaymentMethod;
use Modules\Supplier\Domain\Exceptions\InvalidPurchaseOrderStateException;
use Modules\Supplier\Domain\Exceptions\OverPaymentException;
use Modules\Supplier\Infrastructure\Requests\StoreSupplierPaymentRequest;
use Modules\Supplier\Infrastructure\Resources\SupplierPaymentResource;

class SupplierPaymentController extends Controller
{
    public function __construct(
        private SupplierPaymentRepositoryInterface $paymentRepository,
        private RecordPaymentAction $recordPaymentAction,
    ) {}

    public function indexByPO(int $id): JsonResponse
    {
        $payments = $this->paymentRepository->findByPurchaseOrder($id);

        return response()->json([
            'data' => SupplierPaymentResource::collection($payments),
        ]);
    }

    public function indexBySupplier(int $id): JsonResponse
    {
        $result = $this->paymentRepository->findBySupplierPaginated($id, 15);

        return response()->json([
            'data' => SupplierPaymentResource::collection($result['data']),
            'meta' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
            ],
        ]);
    }

    public function store(StoreSupplierPaymentRequest $request, int $id): JsonResponse
    {
        $data = new SupplierPaymentData(
            purchaseOrderId: $id,
            amount: $request->validated('amount'),
            paymentDate: $request->validated('payment_date'),
            paymentMethod: PaymentMethod::from($request->validated('payment_method')),
            notes: $request->validated('notes'),
        );

        try {
            $payment = $this->recordPaymentAction->execute($data);
        } catch (OverPaymentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (InvalidPurchaseOrderStateException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => new SupplierPaymentResource($payment),
        ], 201);
    }
}
