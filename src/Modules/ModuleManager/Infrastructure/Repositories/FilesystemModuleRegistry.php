<?php

namespace Modules\ModuleManager\Infrastructure\Repositories;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;
use Modules\ModuleManager\Domain\Entities\ModuleManifest;

class FilesystemModuleRegistry implements ModuleRegistryInterface
{
    private ?array $cache = null;

    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $basePath = $this->basePath();
        $modules = [];

        foreach (glob("{$basePath}/*", GLOB_ONLYDIR) as $dir) {
            $name = basename($dir);
            $manifestPath = "{$dir}/module.json";
            $hasManifest = file_exists($manifestPath);

            if ($hasManifest) {
                $data = json_decode(file_get_contents($manifestPath), true);
                $modules[] = new ModuleManifest(
                    name: $data['name'] ?? $name,
                    displayName: $data['display_name'] ?? $name,
                    description: $data['description'] ?? '',
                    depends: $data['depends'] ?? [],
                    extractable: $data['extractable'] ?? true,
                    tags: $data['tags'] ?? [],
                    path: $dir,
                    hasManifest: true,
                );
            } else {
                $modules[] = new ModuleManifest(
                    name: $name,
                    displayName: $name,
                    description: '',
                    depends: [],
                    extractable: true,
                    tags: [],
                    path: $dir,
                    hasManifest: false,
                );
            }
        }

        $this->cache = $modules;

        return $modules;
    }

    public function find(string $name): ?ModuleManifest
    {
        foreach ($this->all() as $module) {
            if ($module->name === $name) {
                return $module;
            }
        }

        return null;
    }

    public function basePath(): string
    {
        return base_path('src/Modules');
    }
}
