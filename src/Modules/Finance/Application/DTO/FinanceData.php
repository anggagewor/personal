<?php

namespace Modules\Finance\Application\DTO;

readonly class FinanceData
{
    public function __construct(
        public string $type,
        public float $amount,
        public ?string $category = null,
        public ?string $description = null,
        public ?string $date = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            amount: (float) $data['amount'],
            category: $data['category'] ?? null,
            description: $data['description'] ?? null,
            date: $data['date'] ?? null,
        );
    }
}
