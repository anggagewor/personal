<?php

namespace Modules\Quote\Application\DTO;

readonly class QuoteData
{
    public function __construct(
        public string $content,
        public ?string $author = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
            author: $data['author'] ?? null,
        );
    }
}
