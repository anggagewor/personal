<?php

namespace Modules\Accounting\Application\Actions;

use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;

class DeleteJournalEntryAction
{
    public function __construct(
        private JournalEntryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): void
    {
        $this->repository->delete($id);
    }
}
