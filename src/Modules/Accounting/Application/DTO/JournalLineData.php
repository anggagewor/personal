<?php

namespace Modules\Accounting\Application\DTO;

readonly class JournalLineData
{
    public function __construct(
        public int $accountId,
        public float $debit,
        public float $credit,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accountId: (int) $data['account_id'],
            debit: (float) ($data['debit'] ?? 0),
            credit: (float) ($data['credit'] ?? 0),
        );
    }
}
