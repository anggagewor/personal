<?php

namespace Modules\Pos\Application\Actions\Voucher;

use Modules\Pos\Application\DTO\VoucherData;
use Modules\Pos\Domain\Contracts\VoucherRepositoryInterface;
use Modules\Pos\Domain\Entities\Voucher;

class CreateVoucherAction
{
    public function __construct(
        private VoucherRepositoryInterface $voucherRepo,
    ) {}

    public function execute(int $outletId, VoucherData $data): Voucher
    {
        return $this->voucherRepo->create($outletId, $data);
    }
}
