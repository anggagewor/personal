<?php

namespace Modules\Activity\Application\Actions;

use Modules\Activity\Application\DTO\ActivityLogData;
use Modules\Activity\Domain\Contracts\ActivityLogRepositoryInterface;
use Modules\Activity\Domain\Entities\ActivityLog;

class LogActivityAction
{
    public function __construct(
        private ActivityLogRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, ActivityLogData $data): ActivityLog
    {
        $activityLog = new ActivityLog(
            id: null,
            userId: $userId,
            type: $data->type,
            description: $data->description,
            properties: $data->properties,
        );

        return $this->repository->save($activityLog);
    }
}
