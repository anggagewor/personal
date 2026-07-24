<?php

namespace Modules\Task\Domain\Entities;

use DateTimeImmutable;
use Modules\Task\Domain\Enums\TaskPriority;
use Modules\Task\Domain\Enums\TaskStatus;

class Task
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public ?string $description = null,
        public TaskStatus $status = TaskStatus::Pending,
        public TaskPriority $priority = TaskPriority::Medium,
        public ?DateTimeImmutable $dueDate = null,
        public int $position = 0,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?DateTimeImmutable $deletedAt = null,
    ) {}

    public function complete(): void
    {
        $this->status = TaskStatus::Completed;
    }

    public function start(): void
    {
        $this->status = TaskStatus::InProgress;
    }
}
