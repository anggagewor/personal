<?php

namespace Modules\Scratchpad\Application\Actions;

use Modules\Scratchpad\Application\DTO\ScratchpadData;
use Modules\Scratchpad\Domain\Contracts\ScratchpadRepositoryInterface;
use Modules\Scratchpad\Domain\Entities\Scratchpad;

class UpdateScratchpadAction
{
    public function __construct(
        private ScratchpadRepositoryInterface $repository,
    ) {}

    public function execute(int $scratchpadId, ScratchpadData $data): Scratchpad
    {
        $scratchpad = $this->repository->findById($scratchpadId);

        $scratchpad->content = $data->content ?? $scratchpad->content;
        $scratchpad->color = $data->color ?? $scratchpad->color;
        $scratchpad->position = $data->position;

        return $this->repository->save($scratchpad);
    }
}
