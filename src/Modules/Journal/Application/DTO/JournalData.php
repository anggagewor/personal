<?php

namespace Modules\Journal\Application\DTO;

readonly class JournalData
{
    public function __construct(
        public string $content,
        public ?string $mood = null,
        public ?string $date = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
            mood: $data['mood'] ?? null,
            date: $data['date'] ?? null,
        );
    }
}
