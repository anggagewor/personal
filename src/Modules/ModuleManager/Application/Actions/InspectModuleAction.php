<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class InspectModuleAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
    ) {}

    /**
     * Inspect a module: count files per DDD layer and type.
     */
    public function execute(string $moduleName): ?array
    {
        $module = $this->registry->find($moduleName);
        if (!$module) {
            return null;
        }

        $path = $module->path;
        $counts = [
            'entities' => $this->countPhpFiles("{$path}/Domain/Entities"),
            'contracts' => $this->countPhpFiles("{$path}/Domain/Contracts"),
            'value_objects' => $this->countPhpFiles("{$path}/Domain/ValueObjects"),
            'enums' => $this->countPhpFiles("{$path}/Domain/Enums"),
            'events' => $this->countPhpFiles("{$path}/Domain/Events"),
            'actions' => $this->countPhpFiles("{$path}/Application/Actions"),
            'dtos' => $this->countPhpFiles("{$path}/Application/DTO"),
            'queries' => $this->countPhpFiles("{$path}/Application/Queries"),
            'controllers' => $this->countPhpFiles("{$path}/Infrastructure/Controllers"),
            'models' => $this->countPhpFiles("{$path}/Infrastructure/Models"),
            'repositories' => $this->countPhpFiles("{$path}/Infrastructure/Repositories"),
            'requests' => $this->countPhpFiles("{$path}/Infrastructure/Requests"),
            'resources' => $this->countPhpFiles("{$path}/Infrastructure/Resources"),
            'commands' => $this->countPhpFiles("{$path}/Infrastructure/Commands"),
            'migrations' => $this->countPhpFiles("{$path}/Infrastructure/Migrations"),
        ];

        // Count tests
        $testPaths = [
            base_path("tests/Feature/{$moduleName}"),
            base_path("tests/Unit/{$moduleName}"),
        ];
        $testCount = 0;
        foreach ($testPaths as $testPath) {
            $testCount += $this->countPhpFiles($testPath);
        }
        $counts['tests'] = $testCount;

        // Total PHP files in module
        $counts['total_files'] = $this->countPhpFilesRecursive($path);

        // Directory size estimate
        $counts['size_bytes'] = $this->getDirectorySize($path);

        // DDD layers present
        $counts['has_domain'] = is_dir("{$path}/Domain");
        $counts['has_application'] = is_dir("{$path}/Application");
        $counts['has_infrastructure'] = is_dir("{$path}/Infrastructure");

        return $counts;
    }

    private function countPhpFiles(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;
        foreach (glob("{$dir}/*.php") as $file) {
            if (is_file($file)) {
                $count++;
            }
        }

        return $count;
    }

    private function countPhpFilesRecursive(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $count++;
            }
        }

        return $count;
    }

    private function getDirectorySize(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }
}
