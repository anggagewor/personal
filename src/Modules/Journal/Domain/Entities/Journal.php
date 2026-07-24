<?php

namespace Modules\Journal\Domain\Entities;

use DateTimeImmutable;
use Modules\Journal\Domain\Enums\JournalMood;

class Journal
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $content,
        public ?JournalMood $mood = null,
        public ?DateTimeImmutable $date = null,
        public ?DateTimeImmutable $createdAt = null,
    ) {}
}
