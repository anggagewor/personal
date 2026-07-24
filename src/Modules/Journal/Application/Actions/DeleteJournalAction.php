<?php

namespace Modules\Journal\Application\Actions;

use Modules\Journal\Domain\Contracts\JournalRepositoryInterface;

class DeleteJournalAction
{
    public function __construct(
        private JournalRepositoryInterface $repository,
    ) {}

    public function execute(int $journalId): void
    {
        $this->repository->delete($journalId);
    }
}
