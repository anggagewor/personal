<?php

namespace Modules\Journal\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Journal\Application\Actions\CreateJournalAction;
use Modules\Journal\Application\Actions\DeleteJournalAction;
use Modules\Journal\Application\DTO\JournalData;
use Modules\Journal\Domain\Contracts\JournalRepositoryInterface;
use Modules\Journal\Infrastructure\Requests\StoreJournalRequest;

class JournalController extends Controller
{
    public function __construct(
        private JournalRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            month: $request->query('month'),
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreJournalRequest $request, CreateJournalAction $action): JsonResponse
    {
        $journal = $action->execute(
            userId: $request->user()->id,
            data: JournalData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $journal,
            'message' => 'Jurnal berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request, string $date): JsonResponse
    {
        $journal = $this->repository->findByDate(
            userId: $request->user()->id,
            date: $date,
        );

        if (!$journal) {
            return response()->json([
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => $journal,
        ]);
    }

    public function destroy(Request $request, int $id, DeleteJournalAction $action): JsonResponse
    {
        $journal = $this->repository->findById($id);

        if (!$journal || $journal->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Jurnal berhasil dihapus.',
        ]);
    }

    public function moods(Request $request): JsonResponse
    {
        $moods = $this->repository->findMoodsByUser($request->user()->id);

        return response()->json([
            'data' => $moods,
        ]);
    }
}
