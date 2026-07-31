<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\DiscountData;
use Modules\Pos\Domain\Entities\Discount;

interface DiscountRepositoryInterface
{
    public function findByOutlet(int $outletId): array;

    public function findActiveByOutlet(int $outletId): array;

    public function findById(int $id): ?Discount;

    public function create(int $outletId, DiscountData $data): Discount;

    public function update(int $id, DiscountData $data): Discount;

    public function delete(int $id): void;

    public function findApplicable(int $outletId, float $subtotal, ?int $memberId = null): array;
}
