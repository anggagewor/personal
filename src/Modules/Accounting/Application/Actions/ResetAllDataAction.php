<?php

namespace Modules\Accounting\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Domain\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;

class ResetAllDataAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
        private AccountRepositoryInterface $accountRepository,
        private ProvisionDefaultAccountsAction $provisionAction,
    ) {}

    /**
     * Delete all journal entries and accounts for a user, then re-provision defaults.
     */
    public function execute(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            $this->journalEntryRepository->deleteAllByUser($userId);
            $this->accountRepository->deleteAllByUser($userId);
        });

        $this->provisionAction->execute($userId);
    }
}
