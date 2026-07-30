<?php

namespace Modules\Accounting\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Actions\CreateJournalEntryAction;
use Modules\Accounting\Application\Actions\DeleteJournalEntryAction;
use Modules\Accounting\Application\Actions\UpdateJournalEntryAction;
use Modules\Accounting\Application\DTO\JournalEntryData;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Exceptions\UnbalancedEntryException;
use Modules\Accounting\Infrastructure\Requests\StoreJournalEntryRequest;
use Modules\Accounting\Infrastructure\Requests\UpdateJournalEntryRequest;
use Modules\Accounting\Infrastructure\Resources\JournalEntryResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class JournalEntryController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private JournalEntryRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => JournalEntryResource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $entry = $this->findOwnedOrFail($this->repository, $id, $request);

        return response()->json([
            'data' => JournalEntryResource::toArray($entry),
        ]);
    }

    public function store(StoreJournalEntryRequest $request, CreateJournalEntryAction $action): JsonResponse
    {
        try {
            $entry = $action->execute(
                userId: $request->user()->id,
                data: JournalEntryData::fromArray($request->validated()),
            );
        } catch (UnbalancedEntryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'balance' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'lines' => [$e->getMessage()],
                ],
            ], 422);
        }

        return response()->json([
            'data' => JournalEntryResource::toArray($entry),
            'message' => 'Jurnal berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateJournalEntryRequest $request, int $id, UpdateJournalEntryAction $action): JsonResponse
    {
        $entry = $this->findOwnedOrFail($this->repository, $id, $request);

        try {
            $entry = $action->execute(
                existing: $entry,
                data: JournalEntryData::fromArray($request->validated()),
            );
        } catch (UnbalancedEntryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'balance' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'lines' => [$e->getMessage()],
                ],
            ], 422);
        }

        return response()->json([
            'data' => JournalEntryResource::toArray($entry),
            'message' => 'Jurnal berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteJournalEntryAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Jurnal berhasil dihapus.',
        ]);
    }
}
