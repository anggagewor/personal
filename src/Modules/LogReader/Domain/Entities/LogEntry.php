<?php

namespace Modules\LogReader\Domain\Entities;

use DateTimeImmutable;
use Modules\LogReader\Domain\Enums\LogLevel;

class LogEntry
{
    public function __construct(
        public readonly DateTimeImmutable $datetime,
        public readonly LogLevel $level,
        public readonly string $environment,
        public readonly string $message,
        public readonly string $stackTrace = '',
        public readonly array $context = [],
    ) {}
}
