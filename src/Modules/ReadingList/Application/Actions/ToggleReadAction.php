<?php

namespace Modules\ReadingList\Application\Actions;

use Modules\ReadingList\Domain\Contracts\ReadingListRepositoryInterface;
use Modules\ReadingList\Domain\Entities\ReadingItem;

class ToggleReadAction
{
    public function __construct(
        private ReadingListRepositoryInterface $repository,
    ) {}

    public function execute(int $itemId): ReadingItem
    {
        $item = $this->repository->findById($itemId);
        $item->toggleRead();

        return $this->repository->save($item);
    }
}
