<?php

namespace Modules\Bookmark\Application\DTO;

class BookmarkData
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
        public readonly ?string $icon = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            url: $data['url'],
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            icon: $data['icon'] ?? null,
        );
    }
}
