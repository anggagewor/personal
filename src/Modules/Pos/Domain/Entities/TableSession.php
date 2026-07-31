<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\TableSessionStatus;

class TableSession
{
    public function __construct(
        public ?int $id,
        public int $tableId,
        public TableSessionStatus $status = TableSessionStatus::Active,
        public ?DateTimeImmutable $startedAt = null,
        public ?DateTimeImmutable $closedAt = null,
    ) {}
}
