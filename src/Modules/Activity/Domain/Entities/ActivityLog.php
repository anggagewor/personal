<?php

namespace Modules\Activity\Domain\Entities;

use DateTimeImmutable;

class ActivityLog
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $action,
        public string $description,
        public array $metadata = [],
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
