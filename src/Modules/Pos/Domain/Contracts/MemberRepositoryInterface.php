<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\MemberData;
use Modules\Pos\Domain\Entities\Member;

interface MemberRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array;

    public function findByOutlet(int $outletId): array;

    public function findById(int $id): ?Member;

    public function create(int $outletId, MemberData $data): Member;

    public function update(int $id, MemberData $data): Member;

    public function delete(int $id): void;

    public function search(int $outletId, string $query): array;
}
