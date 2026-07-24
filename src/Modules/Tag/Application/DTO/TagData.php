<?php

namespace Modules\Tag\Application\DTO;

readonly class TagData
{
    public function __construct(
        public string $name,
        public ?string $color = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            color: $data['color'] ?? null,
        );
    }
}
