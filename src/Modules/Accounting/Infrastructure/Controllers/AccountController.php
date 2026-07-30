<?php

namespace Modules\Accounting\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Actions\CreateAccountAction;
use Modules\Accounting\Application\Actions\DeleteAccountAction;
use Modules\Accounting\Application\Actions\ProvisionDefaultAccountsAction;
use Modules\Accounting\Application\Actions\UpdateAccountAction;
use Modules\Accounting\Application\DTO\AccountData;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Exceptions\AccountInUseException;
use Modules\Accounting\Domain\Exceptions\DuplicateAccountCodeException;
use Modules\Accounting\Domain\Exceptions\MaxDepthExceededException;
use Modules\Accounting\Infrastructure\Requests\StoreAccountRequest;
use Modules\Accounting\Infrastructure\Requests\UpdateAccountRequest;
use Modules\Accounting\Infrastructure\Resources\AccountResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class AccountController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private AccountRepositoryInterface $repository,
    ) {}

    public function index(Request $request, ProvisionDefaultAccountsAction $provisionAction): JsonResponse
    {
        $userId = $request->user()->id;

        if ($this->repository->countByUser($userId) === 0) {
            $provisionAction->execute($userId);
        }

        $grouped = $this->repository->findByUserGroupedByType($userId);

        $data = [];
        foreach ($grouped as $type => $accounts) {
            $data[$type] = AccountResource::collection($accounts);
        }

        return response()->json([
            'data' => $data,
        ]);
    }

    public function store(StoreAccountRequest $request, CreateAccountAction $action): JsonResponse
    {
        try {
            $account = $action->execute(
                userId: $request->user()->id,
                data: AccountData::fromArray($request->validated()),
            );
        } catch (DuplicateAccountCodeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (MaxDepthExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => AccountResource::toArray($account),
            'message' => 'Akun berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateAccountRequest $request, int $id, UpdateAccountAction $action): JsonResponse
    {
        $account = $this->findOwnedOrFail($this->repository, $id, $request);

        $data = AccountData::fromArray(array_merge(
            [
                'code' => $account->code,
                'type' => $account->type->value,
            ],
            $request->validated(),
        ));

        try {
            $account = $action->execute($account, $data);
        } catch (MaxDepthExceededException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => AccountResource::toArray($account),
            'message' => 'Akun berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteAccountAction $action): JsonResponse
    {
        $account = $this->findOwnedOrFail($this->repository, $id, $request);

        try {
            $action->execute($account);
        } catch (AccountInUseException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Akun berhasil dihapus.',
        ]);
    }
}
