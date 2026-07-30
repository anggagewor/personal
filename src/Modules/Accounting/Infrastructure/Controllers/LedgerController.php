<?php

namespace Modules\Accounting\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Actions\GetLedgerAction;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Infrastructure\Resources\AccountResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class LedgerController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private AccountRepositoryInterface $accountRepository,
    ) {}

    public function show(Request $request, int $accountId, GetLedgerAction $action): JsonResponse
    {
        $account = $this->findOwnedOrFail($this->accountRepository, $accountId, $request);

        $result = $action->execute(
            accountId: $accountId,
            startDate: $request->query('start_date'),
            endDate: $request->query('end_date'),
        );

        return response()->json([
            'data' => [
                'account' => AccountResource::toArray($account),
                ...$result,
            ],
        ]);
    }
}
