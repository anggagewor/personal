<?php

namespace Modules\Journal\Application\DTO;

class JournalData
{
    public function __construct(
        public readonly string $content,
        public readonly ?string $mood = null,
        public readonly ?string $date = null,
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
