<?php

namespace Modules\Shared\Domain\Contracts;

interface BaseRepositoryInterface
{
    public function findById(int $id): mixed;

    public function save(mixed $entity): mixed;

    public function delete(int $id): void;
}
