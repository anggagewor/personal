<?php

namespace Modules\Note\Application\DTO;

readonly class NoteData
{
    public function __construct(
        public string $title,
        public string $content,
        public bool $isPinned = false,
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
