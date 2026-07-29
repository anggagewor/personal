<?php

namespace Modules\Budget\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Budget\Application\Actions\CreateBudgetAction;
use Modules\Budget\Application\Actions\DeleteBudgetAction;
use Modules\Budget\Application\Actions\UpdateBudgetAction;
use Modules\Budget\Application\DTO\BudgetData;
use Modules\Budget\Application\Queries\GetBudgetSummaryQuery;
use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;
use Modules\Budget\Infrastructure\Requests\StoreBudgetRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class BudgetController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private BudgetRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $month = $request->query('month', date('Y-m'));

        $budgets = $this->repository->findByUserAndMonth(
            userId: $request->user()->id,
            month: $month,
        );

        return response()->json([
            'data' => $budgets,
        ]);
    }

    public function store(StoreBudgetRequest $request, CreateBudgetAction $action): JsonResponse
    {
        $budget = $action->execute(
            userId: $request->user()->id,
            data: BudgetData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $budget,
            'message' => 'Budget berhasil disimpan.',
        ], 201);
    }

    public function update(StoreBudgetRequest $request, int $id, UpdateBudgetAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $budget = $action->execute(
            budgetId: $id,
            data: BudgetData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $budget,
            'message' => 'Budget berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteBudgetAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Budget berhasil dihapus.',
        ]);
    }

    public function summary(Request $request, GetBudgetSummaryQuery $query): JsonResponse
    {
        $month = $request->query('month', date('Y-m'));

        $summary = $query->execute(
            userId: $request->user()->id,
            month: $month,
        );

        return response()->json([
            'data' => $summary,
        ]);
    }
}
