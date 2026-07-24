<?php

namespace Modules\Scratchpad\Application\DTO;

readonly class ScratchpadData
{
    public function __construct(
        public ?string $content = null,
        public ?string $color = null,
        public int $position = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? null,
            color: $data['color'] ?? null,
            position: $data['position'] ?? 0,
        );
    }
}
