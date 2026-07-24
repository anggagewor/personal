<?php

namespace Modules\Note\Application\DTO;

class NoteData
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly bool $isPinned = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            isPinned: $data['is_pinned'] ?? false,
        );
    }
}
