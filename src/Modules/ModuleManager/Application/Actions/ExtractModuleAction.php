<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;
use Modules\ModuleManager\Domain\Entities\ModuleManifest;
use RuntimeException;
use ZipArchive;

class ExtractModuleAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
    ) {}

    /**
     * Extract a module (and its dependencies) into a zip archive.
     *
     * @param string $moduleName Module to extract
     * @param bool $includeDependencies Whether to bundle dependencies
     * @return string Path to the created archive
     */
    public function execute(string $moduleName, bool $includeDependencies = true): string
    {
        $module = $this->registry->find($moduleName);

        if (!$module) {
            throw new RuntimeException("Module '{$moduleName}' not found.");
        }

        if (!$module->extractable) {
            throw new RuntimeException("Module '{$moduleName}' is not extractable.");
        }

        // Resolve which modules to include
        $moduleNames = [$moduleName];

        if ($includeDependencies) {
            $allModules = $this->indexedRegistry();
            $deps = $module->resolveDependencyTree($allModules);
            $moduleNames = array_merge($deps, [$moduleName]);
        }

        // Create archive
        $outputDir = storage_path('app/module-exports');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $filename = strtolower($moduleName) . "_{$timestamp}.zip";
        $archivePath = "{$outputDir}/{$filename}";

        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create archive at '{$archivePath}'.");
        }

        $basePath = $this->registry->basePath();

        foreach ($moduleNames as $name) {
            $mod = $this->registry->find($name);
            if (!$mod) {
                continue;
            }

            $modulePath = $mod->path;
            $this->addDirectoryToZip($zip, $modulePath, "src/Modules/{$name}");
        }

        // Include a manifest of what's in the archive
        $exportManifest = [
            'exported_at' => now()->toIso8601String(),
            'primary_module' => $moduleName,
            'included_modules' => $moduleNames,
            'include_dependencies' => $includeDependencies,
        ];

        $zip->addFromString('export-manifest.json', json_encode($exportManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->close();

        return $archivePath;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $realPath, string $zipPath): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($realPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * @return array<string, ModuleManifest>
     */
    private function indexedRegistry(): array
    {
        $indexed = [];
        foreach ($this->registry->all() as $module) {
            $indexed[$module->name] = $module;
        }

        return $indexed;
    }
}
