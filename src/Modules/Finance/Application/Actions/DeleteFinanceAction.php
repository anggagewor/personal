<?php

namespace Modules\Finance\Application\Actions;

use Modules\Finance\Domain\Contracts\FinanceRepositoryInterface;

class DeleteFinanceAction
{
    public function __construct(
        private FinanceRepositoryInterface $repository,
    ) {}

    public function execute(int $financeId): void
    {
        $this->repository->delete($financeId);
    }
}
