<?php

namespace Modules\Wishlist\Application\Actions;

use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;
use Modules\Wishlist\Domain\Entities\WishlistItem;

class ToggleWishlistItemAction
{
    public function __construct(
        private WishlistRepositoryInterface $repository,
    ) {}

    public function execute(int $itemId): WishlistItem
    {
        $item = $this->repository->findById($itemId);
        $item->toggle();

        return $this->repository->save($item);
    }
}
