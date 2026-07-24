<?php

namespace Modules\Trash\Application\Actions;

use Modules\Trash\Domain\Contracts\TrashRepositoryInterface;
use Modules\Trash\Domain\Entities\TrashItem;

class ListTrashAction
{
    public function __construct(
        private TrashRepositoryInterface $repository,
    ) {}

    /** @return TrashItem[] */
    public function execute(int $userId): array
    {
        return $this->repository->getAll($userId);
    }
}
