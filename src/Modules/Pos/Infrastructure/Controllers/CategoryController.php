<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Catalog\CreateCategoryAction;
use Modules\Pos\Application\Actions\Catalog\DeleteCategoryAction;
use Modules\Pos\Application\Actions\Catalog\ReorderCategoryAction;
use Modules\Pos\Application\Actions\Catalog\UpdateCategoryAction;
use Modules\Pos\Application\DTO\CategoryData;
use Modules\Pos\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Exceptions\DuplicateCategoryException;
use Modules\Pos\Infrastructure\Requests\StoreCategoryRequest;
use Modules\Pos\Infrastructure\Requests\UpdateCategoryRequest;
use Modules\Pos\Infrastructure\Resources\CategoryResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class CategoryController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private CategoryRepositoryInterface $categoryRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $categories = $this->categoryRepo->findByOutlet($outletId);

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request, int $outletId, CreateCategoryAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        try {
            $category = $action->execute(
                outletId: $outletId,
                data: new CategoryData(
                    name: $validated['name'],
                    icon: $validated['icon'] ?? null,
                    sortOrder: $validated['sort_order'] ?? null,
                    parentId: $validated['parent_id'] ?? null,
                ),
            );
        } catch (DuplicateCategoryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'data' => CategoryResource::toArray($category),
            'message' => 'Kategori berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, int $id, UpdateCategoryAction $action): JsonResponse
    {
        $category = $this->categoryRepo->findById($id);

        if (!$category) {
            abort(404, 'Kategori tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $category->outletId, $request);

        $validated = $request->validated();

        try {
            $updated = $action->execute(
                id: $id,
                data: new CategoryData(
                    name: $validated['name'] ?? $category->name,
                    icon: array_key_exists('icon', $validated) ? $validated['icon'] : $category->icon,
                    sortOrder: $validated['sort_order'] ?? $category->sortOrder,
                    parentId: array_key_exists('parent_id', $validated) ? $validated['parent_id'] : $category->parentId,
                ),
            );
        } catch (DuplicateCategoryException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'data' => CategoryResource::toArray($updated),
            'message' => 'Kategori berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteCategoryAction $action): JsonResponse
    {
        $category = $this->categoryRepo->findById($id);

        if (!$category) {
            abort(404, 'Kategori tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $category->outletId, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    public function reorder(Request $request, ReorderCategoryAction $action): JsonResponse
    {
        $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);

        $action->execute($request->input('ordered_ids'));

        return response()->json([
            'message' => 'Urutan kategori berhasil diperbarui.',
        ]);
    }
}
