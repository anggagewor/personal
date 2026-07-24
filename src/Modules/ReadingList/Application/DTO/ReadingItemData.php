<?php

namespace Modules\ReadingList\Application\DTO;

readonly class ReadingItemData
{
    public function __construct(
        public ?string $title = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            url: $data['url'] ?? null,
        );
    }
}
