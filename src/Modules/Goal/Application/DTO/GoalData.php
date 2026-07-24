<?php

namespace Modules\Goal\Application\DTO;

readonly class GoalData
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $targetDate = null,
        public string $status = 'active',
        public array $milestones = [],
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
