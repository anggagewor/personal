<?php

namespace Modules\Wishlist\Application\Actions;

use Modules\Wishlist\Domain\Contracts\WishlistRepositoryInterface;

class DeleteWishlistItemAction
{
    public function __construct(
        private WishlistRepositoryInterface $repository,
    ) {}

    public function execute(int $itemId): void
    {
        $this->repository->delete($itemId);
    }
}
