<?php

namespace Modules\ModuleManager\Domain\Contracts;

use Modules\ModuleManager\Domain\Entities\ModuleManifest;

interface ModuleRegistryInterface
{
    /**
     * @return ModuleManifest[]
     */
    public function all(): array;

    public function find(string $name): ?ModuleManifest;

    /**
     * Get the base path where modules live.
     */
    public function basePath(): string;
}
