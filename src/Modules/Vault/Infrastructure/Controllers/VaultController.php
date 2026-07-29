<?php

namespace Modules\Vault\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Vault\Application\Actions\CreateVaultEntryAction;
use Modules\Vault\Application\Actions\DeleteVaultEntryAction;
use Modules\Vault\Application\Actions\UpdateVaultEntryAction;
use Modules\Vault\Application\DTO\VaultEntryData;
use Modules\Vault\Domain\Contracts\VaultRepositoryInterface;
use Modules\Vault\Infrastructure\Requests\StoreVaultEntryRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class VaultController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private VaultRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $entries = $this->repository->findByUser(
            userId: $request->user()->id,
            search: $request->query('search'),
            category: $request->query('category'),
        );

        return response()->json([
            'data' => $entries,
        ]);
    }

    public function store(StoreVaultEntryRequest $request, CreateVaultEntryAction $action): JsonResponse
    {
        $entry = $action->execute(
            userId: $request->user()->id,
            data: VaultEntryData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $entry,
            'message' => 'Password berhasil disimpan.',
        ], 201);
    }

    public function update(StoreVaultEntryRequest $request, int $id, UpdateVaultEntryAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $entry = $action->execute(
            entryId: $id,
            userId: $request->user()->id,
            data: VaultEntryData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $entry,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteVaultEntryAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Password berhasil dihapus.',
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = $this->repository->getCategories($request->user()->id);

        return response()->json([
            'data' => $categories,
        ]);
    }
}
