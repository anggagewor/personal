<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Infrastructure\Models\JournalEntryModel;

class ResetJournalDataAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $journalEntryRepository,
    ) {}

    /**
     * Delete all journal entries for a user and return the count of deleted entries.
     */
    public function execute(int $userId): int
    {
        $count = JournalEntryModel::where('user_id', $userId)->count();

        $this->journalEntryRepository->deleteAllByUser($userId);

        return $count;
    }
}
