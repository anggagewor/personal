<?php

namespace Modules\Market\Application\Actions;

use Modules\Market\Application\DTO\WatchlistItemData;
use Modules\Market\Domain\Contracts\WatchlistRepositoryInterface;
use Modules\Market\Domain\Entities\WatchlistItem;

class AddWatchlistItemAction
{
    public function __construct(
        private WatchlistRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, WatchlistItemData $data): WatchlistItem
    {
        $count = $this->repository->countByUser($userId);

        if ($count >= 15) {
            throw new \DomainException('Maksimal 15 simbol di watchlist.');
        }

        $item = new WatchlistItem(
            id: null,
            userId: $userId,
            symbol: $data->symbol,
            type: $data->type,
            label: $data->label,
            position: $data->position ?: $count,
        );

        return $this->repository->save($item);
    }
}
