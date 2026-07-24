<?php

namespace Modules\Wishlist\Application\DTO;

class WishlistItemData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
        );
    }
}
