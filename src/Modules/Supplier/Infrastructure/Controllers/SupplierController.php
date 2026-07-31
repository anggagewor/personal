<?php

namespace Modules\Supplier\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;
use Modules\Supplier\Application\Actions\Supplier\CreateSupplierAction;
use Modules\Supplier\Application\Actions\Supplier\DeleteSupplierAction;
use Modules\Supplier\Application\Actions\Supplier\UpdateSupplierAction;
use Modules\Supplier\Application\DTO\SupplierData;
use Modules\Supplier\Domain\Contracts\SupplierRepositoryInterface;
use Modules\Supplier\Domain\Exceptions\DuplicateSupplierException;
use Modules\Supplier\Infrastructure\Requests\StoreSupplierRequest;
use Modules\Supplier\Infrastructure\Requests\UpdateSupplierRequest;
use Modules\Supplier\Infrastructure\Resources\SupplierListResource;
use Modules\Supplier\Infrastructure\Resources\SupplierResource;

class SupplierController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private SupplierRepositoryInterface $supplierRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $filters = array_filter([
            'search' => $request->query('search'),
        ], fn ($v) => $v !== null);

        $perPage = (int) $request->query('per_page', 15);

        $result = $this->supplierRepo->findByOutletPaginated($outletId, $filters, $perPage);

        // Enrich each supplier with total debt for list display
        $suppliers = array_map(function ($supplier) {
            $supplier->totalDebt = $this->supplierRepo->getTotalDebt($supplier->id);
            return $supplier;
        }, $result['data']);

        return response()->json([
            'data' => SupplierListResource::collection($suppliers),
            'meta' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
            ],
        ]);
    }

    public function store(StoreSupplierRequest $request, int $outletId, CreateSupplierAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $data = new SupplierData(
            name: $request->validated('name'),
            address: $request->validated('address'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            bankName: $request->validated('bank_name'),
            bankAccountNumber: $request->validated('bank_account_number'),
            bankAccountHolder: $request->validated('bank_account_holder'),
            notes: $request->validated('notes'),
        );

        try {
            $supplier = $action->execute($outletId, $data);
        } catch (DuplicateSupplierException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new SupplierResource($supplier),
            'message' => 'Supplier berhasil dibuat.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $supplier = $this->supplierRepo->findById($id);

        if (!$supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $supplier->outletId, $request);

        $supplier->totalDebt = $this->supplierRepo->getTotalDebt($supplier->id);

        return response()->json([
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function update(UpdateSupplierRequest $request, int $id, UpdateSupplierAction $action): JsonResponse
    {
        $supplier = $this->supplierRepo->findById($id);

        if (!$supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $supplier->outletId, $request);

        $data = new SupplierData(
            name: $request->validated('name'),
            address: $request->validated('address'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            bankName: $request->validated('bank_name'),
            bankAccountNumber: $request->validated('bank_account_number'),
            bankAccountHolder: $request->validated('bank_account_holder'),
            notes: $request->validated('notes'),
        );

        try {
            $updated = $action->execute($id, $data);
        } catch (DuplicateSupplierException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => new SupplierResource($updated),
            'message' => 'Supplier berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteSupplierAction $action): JsonResponse
    {
        $supplier = $this->supplierRepo->findById($id);

        if (!$supplier) {
            abort(404, 'Supplier tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $supplier->outletId, $request);

        $action->execute($id);

        return response()->json(null, 204);
    }

    public function search(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $query = $request->query('q', '');

        $suppliers = $this->supplierRepo->search($outletId, $query);

        return response()->json([
            'data' => SupplierListResource::collection($suppliers),
        ]);
    }
}
