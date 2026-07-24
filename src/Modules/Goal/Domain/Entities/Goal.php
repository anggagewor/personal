<?php

namespace Modules\Goal\Domain\Entities;

use DateTimeImmutable;
use Modules\Goal\Domain\Enums\GoalStatus;

class Goal
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public ?string $description = null,
        public ?DateTimeImmutable $targetDate = null,
        public GoalStatus $status = GoalStatus::Active,
        public int $progress = 0,
        public array $milestones = [],
        public ?DateTimeImmutable $createdAt = null,
    ) {}

    public function addMilestone(string $title): void
    {
        $this->milestones[] = [
            'id' => count($this->milestones) + 1,
            'title' => $title,
            'completed' => false,
        ];
        $this->recalculateProgress();
    }

    public function toggleMilestone(int $milestoneId): void
    {
        foreach ($this->milestones as &$milestone) {
            if ($milestone['id'] === $milestoneId) {
                $milestone['completed'] = !$milestone['completed'];
                break;
            }
        }
        $this->recalculateProgress();
    }

    private function recalculateProgress(): void
    {
        if (empty($this->milestones)) {
            $this->progress = 0;
            return;
        }

        $completed = count(array_filter($this->milestones, fn ($m) => $m['completed']));
        $this->progress = (int) round(($completed / count($this->milestones)) * 100);
    }
}
