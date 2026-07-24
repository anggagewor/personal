<?php

namespace Modules\Wishlist\Application\DTO;

readonly class WishlistItemData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $category = null,
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
