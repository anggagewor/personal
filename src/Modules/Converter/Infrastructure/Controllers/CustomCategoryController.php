<?php

namespace Modules\Converter\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Converter\Application\Actions\CreateCustomCategoryAction;
use Modules\Converter\Application\Actions\DeleteCustomCategoryAction;
use Modules\Converter\Application\Actions\UpdateCustomCategoryAction;
use Modules\Converter\Application\DTO\CustomCategoryData;
use Modules\Converter\Domain\Contracts\CustomCategoryRepositoryInterface;
use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;
use Modules\Converter\Infrastructure\Requests\StoreCustomCategoryRequest;
use Modules\Converter\Infrastructure\Requests\UpdateCustomCategoryRequest;
use Modules\Converter\Infrastructure\Resources\CustomCategoryResource;
use Modules\Converter\Infrastructure\Resources\CustomUnitResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class CustomCategoryController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private CustomCategoryRepositoryInterface $categoryRepository,
        private CustomUnitRepositoryInterface $unitRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryRepository->findByUser($request->user()->id);

        $data = array_map(function ($category) {
            $units = $this->unitRepository->findByCategory($category->id);

            return [
                ...CustomCategoryResource::toArray($category),
                'units' => CustomUnitResource::collection($units),
            ];
        }, $categories);

        return response()->json(['data' => $data]);
    }

    public function store(StoreCustomCategoryRequest $request, CreateCustomCategoryAction $action): JsonResponse
    {
        $category = $action->execute(
            userId: $request->user()->id,
            data: CustomCategoryData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => CustomCategoryResource::toArray($category),
            'message' => 'Kategori berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateCustomCategoryRequest $request, int $id, UpdateCustomCategoryAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->categoryRepository, $id, $request);

        $category = $action->execute(
            categoryId: $id,
            data: CustomCategoryData::fromArray(array_merge(
                ['name' => ''],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => CustomCategoryResource::toArray($category),
            'message' => 'Kategori berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteCustomCategoryAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->categoryRepository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
