<?php

namespace Modules\Market\Application\Actions;

use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;

class RemoveWatchlistItemAction
{
    public function __construct(
        private WatchlistRepositoryInterface $repository,
    ) {}

    public function execute(int $itemId): void
    {
        $this->repository->delete($itemId);
    }
}
