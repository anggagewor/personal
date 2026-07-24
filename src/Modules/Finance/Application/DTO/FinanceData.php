<?php

namespace Modules\Finance\Application\DTO;

class FinanceData
{
    public function __construct(
        public readonly string $type,
        public readonly float $amount,
        public readonly ?string $category = null,
        public readonly ?string $description = null,
        public readonly ?string $date = null,
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
