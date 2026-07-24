<?php

namespace Modules\Journal\Application\Actions;

use DateTimeImmutable;
use Modules\Journal\Application\DTO\JournalData;
use Modules\Journal\Domain\Contracts\JournalRepositoryInterface;
use Modules\Journal\Domain\Entities\Journal;
use Modules\Journal\Domain\Enums\JournalMood;

class CreateJournalAction
{
    public function __construct(
        private JournalRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, JournalData $data): Journal
    {
        $journal = new Journal(
            id: null,
            userId: $userId,
            content: $data->content,
            mood: $data->mood ? JournalMood::from($data->mood) : null,
            date: $data->date ? new DateTimeImmutable($data->date) : new DateTimeImmutable(),
        );

        return $this->repository->save($journal);
    }
}
