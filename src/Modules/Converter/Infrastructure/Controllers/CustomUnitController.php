<?php

namespace Modules\Converter\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Converter\Application\Actions\CreateCustomUnitAction;
use Modules\Converter\Application\Actions\DeleteCustomUnitAction;
use Modules\Converter\Application\Actions\UpdateCustomUnitAction;
use Modules\Converter\Application\DTO\CustomUnitData;
use Modules\Converter\Domain\Contracts\CustomCategoryRepositoryInterface;
use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;
use Modules\Converter\Infrastructure\Requests\StoreCustomUnitRequest;
use Modules\Converter\Infrastructure\Requests\UpdateCustomUnitRequest;
use Modules\Converter\Infrastructure\Resources\CustomUnitResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class CustomUnitController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private CustomUnitRepositoryInterface $unitRepository,
        private CustomCategoryRepositoryInterface $categoryRepository,
    ) {}

    public function store(StoreCustomUnitRequest $request, CreateCustomUnitAction $action): JsonResponse
    {
        // Verify user owns the category
        $this->findOwnedOrFail($this->categoryRepository, $request->validated('category_id'), $request);

        $unit = $action->execute(
            data: CustomUnitData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => CustomUnitResource::toArray($unit),
            'message' => 'Satuan berhasil ditambahkan.',
        ], 201);
    }

    public function update(UpdateCustomUnitRequest $request, int $id, UpdateCustomUnitAction $action): JsonResponse
    {
        $existing = $this->unitRepository->findById($id);
        abort_if(!$existing, 404);

        // Verify user owns the category
        $this->findOwnedOrFail($this->categoryRepository, $existing->categoryId, $request);

        $data = array_merge([
            'category_id' => $existing->categoryId,
            'name' => $existing->name,
            'symbol' => $existing->symbol,
            'to_base' => $existing->toBase,
            'is_base' => $existing->isBase,
        ], $request->validated());

        $unit = $action->execute(
            unitId: $id,
            data: CustomUnitData::fromArray($data),
        );

        return response()->json([
            'data' => CustomUnitResource::toArray($unit),
            'message' => 'Satuan berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteCustomUnitAction $action): JsonResponse
    {
        $existing = $this->unitRepository->findById($id);
        abort_if(!$existing, 404);

        // Verify user owns the category
        $this->findOwnedOrFail($this->categoryRepository, $existing->categoryId, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Satuan berhasil dihapus.',
        ]);
    }
}
