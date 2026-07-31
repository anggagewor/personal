<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\QrOrder\CloseTableSessionAction;
use Modules\Pos\Application\Actions\QrOrder\CreateTableAction;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Infrastructure\Requests\StoreTableRequest;
use Modules\Pos\Infrastructure\Resources\TableResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class TableController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private TableRepositoryInterface $tableRepository,
        private OutletRepositoryInterface $outletRepository,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $tables = $this->tableRepository->findByOutlet($outletId);

        return response()->json([
            'data' => TableResource::collection($tables),
            'message' => 'Daftar meja berhasil diambil.',
        ]);
    }

    public function store(StoreTableRequest $request, int $outletId, CreateTableAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $table = $action->execute($outletId, $request->validated('name'));

        return response()->json([
            'data' => TableResource::toArray($table),
            'message' => 'Meja berhasil dibuat.',
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $table = $this->tableRepository->findById($id);

        if (! $table) {
            abort(404, 'Meja tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepository, $table->outletId, $request);

        $this->tableRepository->delete($id);

        return response()->json([
            'message' => 'Meja berhasil dihapus.',
        ]);
    }

    public function closeSession(Request $request, int $id, CloseTableSessionAction $action): JsonResponse
    {
        $table = $this->tableRepository->findById($id);

        if (! $table) {
            abort(404, 'Meja tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepository, $table->outletId, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Sesi meja berhasil ditutup.',
        ]);
    }
}
