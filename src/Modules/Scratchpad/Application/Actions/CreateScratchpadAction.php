<?php

namespace Modules\Scratchpad\Application\Actions;

use Modules\Scratchpad\Application\DTO\ScratchpadData;
use Modules\Scratchpad\Domain\Contracts\ScratchpadRepositoryInterface;
use Modules\Scratchpad\Domain\Entities\Scratchpad;

class CreateScratchpadAction
{
    public function __construct(
        private ScratchpadRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, ScratchpadData $data): Scratchpad
    {
        $scratchpad = new Scratchpad(
            id: null,
            userId: $userId,
            content: $data->content,
            color: $data->color,
            position: $data->position,
        );

        return $this->repository->save($scratchpad);
    }
}
