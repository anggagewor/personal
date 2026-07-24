<?php

namespace Modules\Trash\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Trash\Domain\Contracts\TrashRepositoryInterface;

class TrashController extends Controller
{
    public function __construct(
        private TrashRepositoryInterface $repository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->repository->getAll($request->user()->id);

        return response()->json([
            'data' => $items,
        ]);
    }

    public function restore(Request $request, string $type, int $id): JsonResponse
    {
        if (!in_array($type, ['note', 'task'])) {
            abort(404);
        }

        $restored = $this->repository->restore($type, $id, $request->user()->id);

        if (!$restored) {
            abort(404);
        }

        return response()->json([
            'message' => 'Item berhasil dipulihkan.',
        ]);
    }

    public function forceDelete(Request $request, string $type, int $id): JsonResponse
    {
        if (!in_array($type, ['note', 'task'])) {
            abort(404);
        }

        $deleted = $this->repository->forceDelete($type, $id, $request->user()->id);

        if (!$deleted) {
            abort(404);
        }

        return response()->json([
            'message' => 'Item berhasil dihapus permanen.',
        ]);
    }
}
