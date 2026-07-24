<?php

namespace Modules\Scratchpad\Domain\Contracts;

use Modules\Scratchpad\Domain\Entities\Scratchpad;

interface ScratchpadRepositoryInterface
{
    public function findById(int $id): ?Scratchpad;

    public function findByUser(int $userId): array;

    public function save(Scratchpad $scratchpad): Scratchpad;

    public function delete(int $id): void;
}
