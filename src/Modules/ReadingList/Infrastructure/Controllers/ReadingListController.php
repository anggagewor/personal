<?php

namespace Modules\ReadingList\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ReadingList\Application\Actions\CreateReadingItemAction;
use Modules\ReadingList\Application\Actions\DeleteReadingItemAction;
use Modules\ReadingList\Application\Actions\ToggleFavoriteAction;
use Modules\ReadingList\Application\Actions\ToggleReadAction;
use Modules\ReadingList\Application\DTO\ReadingItemData;
use Modules\ReadingList\Domain\Contracts\ReadingListRepositoryInterface;
use Modules\ReadingList\Infrastructure\Requests\StoreReadingItemRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class ReadingListController extends Controller
{
    use AuthorizesOwnership;
    public function __construct(
        private ReadingListRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isRead = $request->has('is_read') ? $request->boolean('is_read') : null;
        $isFavorite = $request->has('is_favorite') ? $request->boolean('is_favorite') : null;

        $result = $this->repository->findByUserPaginated(
            userId: $request->user()->id,
            isRead: $isRead,
            isFavorite: $isFavorite,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreReadingItemRequest $request, CreateReadingItemAction $action): JsonResponse
    {
        $item = $action->execute(
            userId: $request->user()->id,
            data: ReadingItemData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $item,
            'message' => 'Item bacaan berhasil ditambahkan.',
        ], 201);
    }

    public function destroy(Request $request, int $id, DeleteReadingItemAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Item bacaan berhasil dihapus.',
        ]);
    }

    public function toggleRead(Request $request, int $id, ToggleReadAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $item = $action->execute($id);

        return response()->json([
            'data' => $item,
            'message' => $item->isRead ? 'Ditandai sudah dibaca.' : 'Ditandai belum dibaca.',
        ]);
    }

    public function toggleFavorite(Request $request, int $id, ToggleFavoriteAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $item = $action->execute($id);

        return response()->json([
            'data' => $item,
            'message' => $item->isFavorite ? 'Ditambahkan ke favorit.' : 'Dihapus dari favorit.',
        ]);
    }
}
