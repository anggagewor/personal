<?php

namespace Modules\Market\Application\DTO;

readonly class WatchlistItemData
{
    public function __construct(
        public string $symbol,
        public string $type,
        public ?string $label = null,
        public int $position = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            symbol: strtoupper(str_replace(' ', '', $data['symbol'])),
            type: $data['type'],
            label: $data['label'] ?? null,
            position: $data['position'] ?? 0,
        );
    }
}
