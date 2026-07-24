<?php

namespace Modules\ReadingList\Application\DTO;

class ReadingItemData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            url: $data['url'] ?? null,
        );
    }
}
