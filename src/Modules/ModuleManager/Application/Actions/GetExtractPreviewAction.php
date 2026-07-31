<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;
use Modules\ModuleManager\Domain\Entities\ModuleManifest;

class GetExtractPreviewAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
        private InspectModuleAction $inspector,
    ) {}

    /**
     * Preview what would be included in an extract.
     */
    public function execute(string $moduleName, bool $includeDependencies = true): ?array
    {
        $module = $this->registry->find($moduleName);
        if (!$module) {
            return null;
        }

        $moduleNames = [$moduleName];

        if ($includeDependencies) {
            $allModules = [];
            foreach ($this->registry->all() as $m) {
                $allModules[$m->name] = $m;
            }
            $deps = $module->resolveDependencyTree($allModules);
            $moduleNames = array_merge($deps, [$moduleName]);
        }

        $totalFiles = 0;
        $totalMigrations = 0;
        $totalTests = 0;
        $totalSize = 0;
        $included = [];

        foreach ($moduleNames as $name) {
            $inspection = $this->inspector->execute($name);
            if (!$inspection) {
                continue;
            }

            $files = $inspection['total_files'] ?? 0;
            $migrations = $inspection['migrations'] ?? 0;
            $tests = $inspection['tests'] ?? 0;
            $size = $inspection['size_bytes'] ?? 0;

            $totalFiles += $files;
            $totalMigrations += $migrations;
            $totalTests += $tests;
            $totalSize += $size;

            $included[] = [
                'name' => $name,
                'files' => $files,
                'migrations' => $migrations,
                'tests' => $tests,
                'size_bytes' => $size,
            ];
        }

        return [
            'module' => $moduleName,
            'include_dependencies' => $includeDependencies,
            'included_modules' => $included,
            'totals' => [
                'modules' => count($moduleNames),
                'files' => $totalFiles,
                'migrations' => $totalMigrations,
                'tests' => $totalTests,
                'size_bytes' => $totalSize,
            ],
        ];
    }
}
