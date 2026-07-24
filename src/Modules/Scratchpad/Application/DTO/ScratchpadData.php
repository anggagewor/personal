<?php

namespace Modules\Scratchpad\Application\DTO;

class ScratchpadData
{
    public function __construct(
        public readonly ?string $content = null,
        public readonly ?string $color = null,
        public readonly int $position = 0,
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
