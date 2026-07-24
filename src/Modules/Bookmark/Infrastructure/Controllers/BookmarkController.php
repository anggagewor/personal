<?php

namespace Modules\Bookmark\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Bookmark\Application\Actions\CreateBookmarkAction;
use Modules\Bookmark\Application\Actions\DeleteBookmarkAction;
use Modules\Bookmark\Application\Actions\UpdateBookmarkAction;
use Modules\Bookmark\Application\DTO\BookmarkData;
use Modules\Bookmark\Domain\Contracts\BookmarkRepositoryInterface;
use Modules\Bookmark\Infrastructure\Requests\StoreBookmarkRequest;
use Modules\Bookmark\Infrastructure\Requests\UpdateBookmarkRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class BookmarkController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private BookmarkRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $grouped = $this->repository->findByUserGroupedByCategory(
            userId: $request->user()->id,
        );

        return response()->json([
            'data' => $grouped,
        ]);
    }

    public function store(StoreBookmarkRequest $request, CreateBookmarkAction $action): JsonResponse
    {
        $bookmark = $action->execute(
            userId: $request->user()->id,
            data: BookmarkData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $bookmark,
            'message' => 'Bookmark berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateBookmarkRequest $request, int $id, UpdateBookmarkAction $action): JsonResponse
    {
        $bookmark = $this->findOwnedOrFail($this->repository, $id, $request);

        $bookmark = $action->execute(
            bookmarkId: $id,
            data: BookmarkData::fromArray(array_merge(
                [
                    'title' => $bookmark->title,
                    'url' => $bookmark->url,
                    'description' => $bookmark->description,
                    'category' => $bookmark->category,
                    'icon' => $bookmark->icon,
                ],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $bookmark,
            'message' => 'Bookmark berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteBookmarkAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Bookmark berhasil dihapus.',
        ]);
    }
}
