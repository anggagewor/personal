<?php

namespace Modules\Wishlist\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Wishlist\Application\Actions\CreateWishlistItemAction;
use Modules\Wishlist\Application\Actions\DeleteWishlistItemAction;
use Modules\Wishlist\Application\Actions\ToggleWishlistItemAction;
use Modules\Wishlist\Application\Actions\UpdateWishlistItemAction;
use Modules\Wishlist\Application\DTO\WishlistItemData;
use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;
use Modules\Wishlist\Infrastructure\Requests\StoreWishlistItemRequest;
use Modules\Wishlist\Infrastructure\Requests\UpdateWishlistItemRequest;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isCompleted = $request->has('is_completed') ? $request->boolean('is_completed') : null;
        $category = $request->query('category');

        $result = $this->repository->findByUserFiltered(
            userId: $request->user()->id,
            isCompleted: $isCompleted,
            category: $category,
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreWishlistItemRequest $request, CreateWishlistItemAction $action): JsonResponse
    {
        $item = $action->execute(
            userId: $request->user()->id,
            data: WishlistItemData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $item,
            'message' => 'Wishlist item berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateWishlistItemRequest $request, int $id, UpdateWishlistItemAction $action): JsonResponse
    {
        $item = $this->repository->findById($id);

        if (!$item || $item->userId !== $request->user()->id) {
            abort(403);
        }

        $item = $action->execute(
            itemId: $id,
            data: WishlistItemData::fromArray(array_merge(
                [
                    'title' => $item->title,
                    'description' => $item->description,
                    'category' => $item->category,
                ],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $item,
            'message' => 'Wishlist item berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteWishlistItemAction $action): JsonResponse
    {
        $item = $this->repository->findById($id);

        if (!$item || $item->userId !== $request->user()->id) {
            abort(403);
        }

        $action->execute($id);

        return response()->json([
            'message' => 'Wishlist item berhasil dihapus.',
        ]);
    }

    public function toggle(Request $request, int $id, ToggleWishlistItemAction $action): JsonResponse
    {
        $item = $this->repository->findById($id);

        if (!$item || $item->userId !== $request->user()->id) {
            abort(403);
        }

        $item = $action->execute($id);

        return response()->json([
            'data' => $item,
            'message' => $item->isCompleted ? 'Item ditandai selesai.' : 'Item ditandai belum selesai.',
        ]);
    }
}
