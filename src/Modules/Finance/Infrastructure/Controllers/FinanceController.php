<?php

namespace Modules\Finance\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Application\Actions\CreateFinanceAction;
use Modules\Finance\Application\Actions\DeleteFinanceAction;
use Modules\Finance\Application\Actions\UpdateFinanceAction;
use Modules\Finance\Application\DTO\FinanceData;
use Modules\Finance\Domain\Contracts\FinanceRepositoryInterface;
use Modules\Finance\Infrastructure\Requests\StoreFinanceRequest;
use Modules\Finance\Infrastructure\Requests\UpdateFinanceRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class FinanceController extends Controller
{
    use AuthorizesOwnership;
    public function __construct(
        private FinanceRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            month: $request->query('month'),
            type: $request->query('type'),
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreFinanceRequest $request, CreateFinanceAction $action): JsonResponse
    {
        $finance = $action->execute(
            userId: $request->user()->id,
            data: FinanceData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $finance,
            'message' => 'Transaksi berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateFinanceRequest $request, int $id, UpdateFinanceAction $action): JsonResponse
    {
        $finance = $this->findOwnedOrFail($this->repository, $id, $request);

        $finance = $action->execute(
            financeId: $id,
            data: FinanceData::fromArray(array_merge(
                [
                    'type' => $finance->type->value,
                    'amount' => $finance->amount,
                    'category' => $finance->category,
                    'description' => $finance->description,
                    'date' => $finance->date?->format('Y-m-d'),
                ],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $finance,
            'message' => 'Transaksi berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteFinanceAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $summary = $this->repository->getSummary(
            userId: $request->user()->id,
            month: $request->query('month'),
        );

        return response()->json([
            'data' => $summary,
        ]);
    }
}
