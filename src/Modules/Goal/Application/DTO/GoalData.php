<?php

namespace Modules\Goal\Application\DTO;

class GoalData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $targetDate = null,
        public readonly string $status = 'active',
        public readonly array $milestones = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            targetDate: $data['target_date'] ?? null,
            status: $data['status'] ?? 'active',
            milestones: $data['milestones'] ?? [],
        );
    }
}
