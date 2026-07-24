<?php

namespace Modules\Activity\Domain\Contracts;

use Modules\Activity\Domain\Entities\ActivityLog;

interface ActivityLogRepositoryInterface
{
    public function findByUserPaginated(int $userId, int $perPage = 15): array;

    public function save(ActivityLog $activityLog): ActivityLog;
}
