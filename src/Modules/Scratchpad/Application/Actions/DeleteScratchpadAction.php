<?php

namespace Modules\Scratchpad\Application\Actions;

use Modules\Scratchpad\Domain\Contracts\ScratchpadRepositoryInterface;

class DeleteScratchpadAction
{
    public function __construct(
        private ScratchpadRepositoryInterface $repository,
    ) {}

    public function execute(int $scratchpadId): void
    {
        $this->repository->delete($scratchpadId);
    }
}
