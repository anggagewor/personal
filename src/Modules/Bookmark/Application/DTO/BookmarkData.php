<?php

namespace Modules\Bookmark\Application\DTO;

readonly class BookmarkData
{
    public function __construct(
        public string $title,
        public string $url,
        public ?string $description = null,
        public ?string $category = null,
        public ?string $icon = null,
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
