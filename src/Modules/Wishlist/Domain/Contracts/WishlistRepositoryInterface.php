<?php

namespace Modules\Wishlist\Domain\Contracts;

use Modules\Wishlist\Domain\Entities\WishlistItem;

interface WishlistRepositoryInterface
{
    public function findById(int $id): ?WishlistItem;

    public function findByUserFiltered(int $userId, ?bool $isCompleted = null, ?string $category = null, int $perPage = 15): array;

    public function save(WishlistItem $item): WishlistItem;

    public function delete(int $id): void;
}
