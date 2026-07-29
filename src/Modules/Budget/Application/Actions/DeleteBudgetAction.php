<?php

namespace Modules\Budget\Application\Actions;

use Modules\Budget\Domain\Contracts\BudgetRepositoryInterface;

class DeleteBudgetAction
{
    public function __construct(
        private BudgetRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
