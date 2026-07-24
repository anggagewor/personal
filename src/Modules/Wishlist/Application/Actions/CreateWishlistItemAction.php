<?php

namespace Modules\Wishlist\Application\Actions;

use Modules\Wishlist\Application\DTO\WishlistItemData;
use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;
use Modules\Wishlist\Domain\Entities\WishlistItem;

class CreateWishlistItemAction
{
    public function __construct(
        private WishlistRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, WishlistItemData $data): WishlistItem
    {
        $item = new WishlistItem(
            id: null,
            userId: $userId,
            title: $data->title,
            description: $data->description,
            category: $data->category,
        );

        return $this->repository->save($item);
    }
}
