<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Modules\Pos\Application\DTO\MemberData;
use Modules\Pos\Domain\Contracts\MemberRepositoryInterface;
use Modules\Pos\Domain\Entities\Member;
use Modules\Pos\Infrastructure\Models\MemberModel;

class EloquentMemberRepository implements MemberRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array
    {
        $query = MemberModel::where('outlet_id', $outletId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('name')->paginate($perPage);

        return [
            'data' => array_map(fn ($model) => $this->toEntity($model), $paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function findByOutlet(int $outletId): array
    {
        return MemberModel::where('outlet_id', $outletId)
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    public function findById(int $id): ?Member
    {
        $model = MemberModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(int $outletId, MemberData $data): Member
    {
        $model = MemberModel::create([
            'outlet_id' => $outletId,
            'name' => $data->name,
            'phone' => $data->phone,
            'email' => $data->email,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, MemberData $data): Member
    {
        $model = MemberModel::findOrFail($id);

        $model->update([
            'name' => $data->name,
            'phone' => $data->phone,
            'email' => $data->email,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): void
    {
        MemberModel::where('id', $id)->delete();
    }

    public function search(int $outletId, string $query): array
    {
        return MemberModel::where('outlet_id', $outletId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    private function toEntity(MemberModel $model): Member
    {
        return new Member(
            id: $model->id,
            outletId: $model->outlet_id,
            name: $model->name,
            phone: $model->phone,
            email: $model->email,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
