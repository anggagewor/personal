<?php

namespace Modules\Note\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Note\Application\Actions\CreateNoteAction;
use Modules\Note\Application\Actions\DeleteNoteAction;
use Modules\Note\Application\Actions\TogglePinNoteAction;
use Modules\Note\Application\Actions\UpdateNoteAction;
use Modules\Note\Application\DTO\NoteData;
use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Infrastructure\Requests\StoreNoteRequest;
use Modules\Note\Infrastructure\Requests\UpdateNoteRequest;
use Modules\Note\Infrastructure\Resources\NoteResource;

class NoteController extends Controller
{
    public function __construct(
        private NoteRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            search: $request->query('search'),
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => NoteResource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreNoteRequest $request, CreateNoteAction $action): JsonResponse
    {
        $note = $action->execute(
            userId: $request->user()->id,
            data: NoteData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => NoteResource::toArray($note),
            'message' => 'Catatan berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $note = $this->repository->findById($id);

        if (!$note || $note->userId !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'data' => NoteResource::toArray($note),
        ]);
    }

    public function update(UpdateNoteRequest $request, int $id, UpdateNoteAction $action): JsonResponse
    {
        $note = $this->repository->findById($id);

        if (!$note || $note->userId !== $request->user()->id) {
            abort(403);
        }

        $note = $action->execute(
            noteId: $id,
            data: NoteData::fromArray(array_merge(
                ['title' => $note->title, 'content' => $note->content, 'is_pinned' => $note->isPinned],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => NoteResource::toArray($note),
            'message' => 'Catatan berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteNoteAction $action): JsonResponse
    {
        $note = $this->repository->findById($id);

        if (!$note || $note->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Catatan berhasil dihapus.',
        ]);
    }

    public function togglePin(Request $request, int $id, TogglePinNoteAction $action): JsonResponse
    {
        $note = $this->repository->findById($id);

        if (!$note || $note->userId !== $request->user()->id) {
            abort(403);
        }

        $note = $action->execute($id);

        return response()->json([
            'data' => NoteResource::toArray($note),
            'message' => $note->isPinned ? 'Catatan disematkan.' : 'Catatan batal disematkan.',
        ]);
    }
}
