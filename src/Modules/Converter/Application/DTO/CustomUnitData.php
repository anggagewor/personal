<?php

namespace Modules\Converter\Application\DTO;

readonly class CustomUnitData
{
    public function __construct(
        public int $categoryId,
        public string $name,
        public string $symbol,
        public float $toBase,
        public bool $isBase = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: $data['category_id'],
            name: $data['name'],
            symbol: $data['symbol'],
            toBase: (float) $data['to_base'],
            isBase: (bool) ($data['is_base'] ?? false),
        );
    }
}
