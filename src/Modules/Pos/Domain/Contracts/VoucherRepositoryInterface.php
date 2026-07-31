<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\VoucherData;
use Modules\Pos\Domain\Entities\Voucher;

interface VoucherRepositoryInterface
{
    public function findByOutletPaginated(int $outletId, int $perPage): array;

    public function findByCode(string $code): ?Voucher;

    public function findById(int $id): ?Voucher;

    public function create(int $outletId, VoucherData $data): Voucher;

    public function batchCreate(int $outletId, array $vouchers): array;

    public function incrementUsage(int $id): void;

    public function recordRedemption(int $voucherId, int $transactionId): void;
}
