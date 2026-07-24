<?php

namespace Modules\Calendar\Domain\Entities;

use DateTimeImmutable;

class Holiday
{
    public function __construct(
        public ?int $id,
        public string $name,
        public DateTimeImmutable $date,
        public string $type,
        public bool $isNational = false,
    ) {}
}
