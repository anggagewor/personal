<?php

namespace Modules\Finance\Domain\Contracts;

use Modules\Finance\Domain\Entities\Finance;

interface FinanceRepositoryInterface
{
    public function findById(int $id): ?Finance;

    public function findByUserPaginated(int $userId, ?string $month = null, ?string $type = null, int $perPage = 15): array;

    public function save(Finance $finance): Finance;

    public function delete(int $id): void;

    public function getSummary(int $userId, ?string $month = null): array;
}
