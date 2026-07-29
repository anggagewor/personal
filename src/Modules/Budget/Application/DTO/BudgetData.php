<?php

namespace Modules\Budget\Application\DTO;

readonly class BudgetData
{
    public function __construct(
        public string $category,
        public float $amount,
        public string $month,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            category: $data['category'],
            amount: (float) $data['amount'],
            month: $data['month'],
        );
    }
}
