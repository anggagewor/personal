<?php

namespace Modules\Task\Application\DTO;

class TaskData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly string $status = 'pending',
        public readonly string $priority = 'medium',
        public readonly ?string $dueDate = null,
        public readonly int $position = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            status: $data['status'] ?? 'pending',
            priority: $data['priority'] ?? 'medium',
            dueDate: $data['due_date'] ?? null,
            position: $data['position'] ?? 0,
        );
    }
}
