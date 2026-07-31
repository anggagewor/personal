<?php

namespace Modules\Pos\Application\Queries;

use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;

class OpenBillsQuery
{
    public function __construct(
        private readonly TransactionRepositoryInterface $transactionRepo,
    ) {}

    public function execute(int $outletId): array
    {
        return $this->transactionRepo->findOpenBillsByOutlet($outletId);
    }
}
