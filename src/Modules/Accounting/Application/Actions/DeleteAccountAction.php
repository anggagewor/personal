<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Accounting\Domain\Exceptions\AccountInUseException;

class DeleteAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $repository,
    ) {}

    public function execute(Account $account): void
    {
        if ($this->repository->hasJournalLines($account->id)) {
            throw AccountInUseException::create($account->code);
        }

        $this->repository->delete($account->id);
    }
}
