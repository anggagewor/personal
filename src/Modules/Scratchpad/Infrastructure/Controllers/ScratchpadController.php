<?php

namespace Modules\Scratchpad\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Scratchpad\Application\Actions\CreateScratchpadAction;
use Modules\Scratchpad\Application\Actions\DeleteScratchpadAction;
use Modules\Scratchpad\Application\Actions\UpdateScratchpadAction;
use Modules\Scratchpad\Application\DTO\ScratchpadData;
use Modules\Scratchpad\Domain\Contracts\ScratchpadRepositoryInterface;
use Modules\Scratchpad\Infrastructure\Requests\StoreScratchpadRequest;
use Modules\Scratchpad\Infrastructure\Requests\UpdateScratchpadRequest;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class ScratchpadController extends Controller
{
    use AuthorizesOwnership;
    public function __construct(
        private ScratchpadRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $scratchpads = $this->repository->findByUser($request->user()->id);

        return response()->json([
            'data' => $scratchpads,
        ]);
    }

    public function store(StoreScratchpadRequest $request, CreateScratchpadAction $action): JsonResponse
    {
        $scratchpad = $action->execute(
            userId: $request->user()->id,
            data: ScratchpadData::fromArray($request->validated()),
        );

        return response()->json([
            'data' => $scratchpad,
            'message' => 'Scratchpad berhasil dibuat.',
        ], 201);
    }

    public function update(UpdateScratchpadRequest $request, int $id, UpdateScratchpadAction $action): JsonResponse
    {
        $scratchpad = $this->findOwnedOrFail($this->repository, $id, $request);

        $scratchpad = $action->execute(
            scratchpadId: $id,
            data: ScratchpadData::fromArray(array_merge(
                ['content' => $scratchpad->content, 'color' => $scratchpad->color, 'position' => $scratchpad->position],
                $request->validated(),
            )),
        );

        return response()->json([
            'data' => $scratchpad,
            'message' => 'Scratchpad berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteScratchpadAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->repository, $id, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Scratchpad berhasil dihapus.',
        ]);
    }
}
