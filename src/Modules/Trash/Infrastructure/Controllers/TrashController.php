<?php

namespace Modules\Trash\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Trash\Application\Actions\ForceDeleteTrashAction;
use Modules\Trash\Application\Actions\ListTrashAction;
use Modules\Trash\Application\Actions\RestoreTrashAction;

class TrashController extends Controller
{
    public function index(Request $request, ListTrashAction $action): JsonResponse
    {
        $items = $action->execute($request->user()->id);

        return response()->json([
            'data' => $items,
        ]);
    }

    public function restore(Request $request, string $type, int $id, RestoreTrashAction $action): JsonResponse
    {
        $action->execute($type, $id, $request->user()->id);

        return response()->json([
            'message' => 'Item berhasil dipulihkan.',
        ]);
    }

    public function forceDelete(Request $request, string $type, int $id, ForceDeleteTrashAction $action): JsonResponse
    {
        $action->execute($type, $id, $request->user()->id);

        return response()->json([
            'message' => 'Item berhasil dihapus permanen.',
        ]);
    }
}
