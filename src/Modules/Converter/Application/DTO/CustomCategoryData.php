<?php

namespace Modules\Converter\Application\DTO;

readonly class CustomCategoryData
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $icon = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
        );
    }
}
