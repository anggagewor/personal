<?php

namespace Modules\Wishlist\Application\Actions;

use Modules\Wishlist\Application\DTO\WishlistItemData;
use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;
use Modules\Wishlist\Domain\Entities\WishlistItem;

class UpdateWishlistItemAction
{
    public function __construct(
        private WishlistRepositoryInterface $repository,
    ) {}

    public function execute(int $itemId, WishlistItemData $data): WishlistItem
    {
        $item = $this->repository->findById($itemId);

        $item->title = $data->title;
        $item->description = $data->description;
        $item->category = $data->category;

        return $this->repository->save($item);
    }
}
