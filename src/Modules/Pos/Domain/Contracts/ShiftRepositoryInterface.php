<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Domain\Entities\CashierShift;

interface ShiftRepositoryInterface
{
    public function findById(int $id): ?CashierShift;

    public function findActiveByOutletAndUser(int $outletId, int $userId): ?CashierShift;

    public function findActiveByOutlet(int $outletId): ?CashierShift;

    public function findByOutletPaginated(int $outletId, array $filters, int $perPage): array;

    public function create(int $outletId, int $userId, string $cashierName, float $openingAmount): CashierShift;

    public function close(int $id, float $closingAmount, float $expectedAmount, ?string $notes): CashierShift;

    public function getCashSalesDuringShift(int $shiftId): float;

    public function getCashRefundsDuringShift(int $shiftId): float;
}
