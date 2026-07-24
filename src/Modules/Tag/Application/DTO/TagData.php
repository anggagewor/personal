<?php

namespace Modules\Tag\Application\DTO;

class TagData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $color = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            color: $data['color'] ?? null,
        );
    }
}
