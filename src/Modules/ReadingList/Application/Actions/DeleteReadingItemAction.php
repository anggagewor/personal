<?php

namespace Modules\ReadingList\Application\Actions;

use Modules\ReadingList\Domain\Contracts\ReadingListRepositoryInterface;

class DeleteReadingItemAction
{
    public function __construct(
        private ReadingListRepositoryInterface $repository,
    ) {}

    public function execute(int $itemId): void
    {
        $this->repository->delete($itemId);
    }
}
