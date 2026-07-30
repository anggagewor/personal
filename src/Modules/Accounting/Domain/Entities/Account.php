<?php

namespace Modules\Accounting\Domain\Entities;

use DateTimeImmutable;
use Modules\Accounting\Domain\Enums\AccountType;
use Modules\Accounting\Domain\Enums\NormalBalance;

class Account
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $code,
        public string $name,
        public AccountType $type,
        public NormalBalance $normalBalance,
        public ?int $parentId = null,
        public int $depth = 1,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
