<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pos\Application\Actions\Member\CreateMemberAction;
use Modules\Pos\Application\Actions\Member\DeleteMemberAction;
use Modules\Pos\Application\Actions\Member\UpdateMemberAction;
use Modules\Pos\Application\DTO\MemberData;
use Modules\Pos\Domain\Contracts\MemberRepositoryInterface;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Infrastructure\Requests\StoreMemberRequest;
use Modules\Pos\Infrastructure\Requests\UpdateMemberRequest;
use Modules\Pos\Infrastructure\Resources\MemberResource;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class MemberController extends Controller
{
    use AuthorizesOwnership;

    public function __construct(
        private MemberRepositoryInterface $memberRepo,
        private OutletRepositoryInterface $outletRepo,
    ) {}

    public function index(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $result = $this->memberRepo->findByOutletPaginated(
            outletId: $outletId,
            filters: [
                'search' => $request->query('search'),
            ],
            perPage: (int) $request->query('per_page', 15),
        );

        return response()->json([
            'data' => MemberResource::collection($result['data']),
            'meta' => $result['meta'],
            'message' => 'Daftar member berhasil diambil.',
        ]);
    }

    public function store(StoreMemberRequest $request, int $outletId, CreateMemberAction $action): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $validated = $request->validated();

        $member = $action->execute($outletId, new MemberData(
            name: $validated['name'],
            phone: $validated['phone'],
            email: $validated['email'] ?? null,
        ));

        return response()->json([
            'data' => MemberResource::toArray($member),
            'message' => 'Member berhasil ditambahkan.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $member = $this->memberRepo->findById($id);

        if (! $member) {
            abort(404, 'Member tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $member->outletId, $request);

        return response()->json([
            'data' => MemberResource::toArray($member),
        ]);
    }

    public function update(UpdateMemberRequest $request, int $id, UpdateMemberAction $action): JsonResponse
    {
        $member = $this->memberRepo->findById($id);

        if (! $member) {
            abort(404, 'Member tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $member->outletId, $request);

        $validated = $request->validated();

        $member = $action->execute($id, new MemberData(
            name: $validated['name'],
            phone: $validated['phone'],
            email: $validated['email'] ?? null,
        ));

        return response()->json([
            'data' => MemberResource::toArray($member),
            'message' => 'Member berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id, DeleteMemberAction $action): JsonResponse
    {
        $member = $this->memberRepo->findById($id);

        if (! $member) {
            abort(404, 'Member tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepo, $member->outletId, $request);

        $action->execute($id);

        return response()->json([
            'message' => 'Member berhasil dihapus.',
        ]);
    }

    public function search(Request $request, int $outletId): JsonResponse
    {
        $this->findOwnedOrFail($this->outletRepo, $outletId, $request);

        $query = $request->query('q', '');

        $members = $this->memberRepo->search($outletId, $query);

        return response()->json([
            'data' => MemberResource::collection($members),
            'message' => 'Pencarian member berhasil.',
        ]);
    }
}
