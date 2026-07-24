<?php

namespace Modules\Task\Application\DTO;

readonly class TaskData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public string $status = 'pending',
        public string $priority = 'medium',
        public ?string $dueDate = null,
        public int $position = 0,
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
