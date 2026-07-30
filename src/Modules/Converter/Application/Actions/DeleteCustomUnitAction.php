<?php

namespace Modules\Converter\Application\Actions;

use Modules\Converter\Domain\Contracts\CustomUnitRepositoryInterface;

class DeleteCustomUnitAction
{
    public function __construct(
        private CustomUnitRepositoryInterface $repository,
    ) {}

    public function execute(int $unitId): void
    {
        $this->repository->delete($unitId);
    }
}
