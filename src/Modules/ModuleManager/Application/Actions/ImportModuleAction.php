<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;
use RuntimeException;
use ZipArchive;

class ImportModuleAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
    ) {}

    /**
     * Import a module from a zip archive.
     *
     * @param string $archivePath Path to the zip file
     * @param bool $force Overwrite existing modules
     * @return array{imported: string[], skipped: string[]}
     */
    public function execute(string $archivePath, bool $force = false): array
    {
        if (!file_exists($archivePath)) {
            throw new RuntimeException("Archive not found: '{$archivePath}'");
        }

        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== true) {
            throw new RuntimeException("Cannot open archive: '{$archivePath}'");
        }

        // Read export manifest
        $manifestJson = $zip->getFromName('export-manifest.json');
        if ($manifestJson === false) {
            $zip->close();
            throw new RuntimeException('Invalid module archive: missing export-manifest.json');
        }

        $manifest = json_decode($manifestJson, true);
        $includedModules = $manifest['included_modules'] ?? [];

        $basePath = $this->registry->basePath();
        $imported = [];
        $skipped = [];

        foreach ($includedModules as $moduleName) {
            $targetPath = "{$basePath}/{$moduleName}";

            if (is_dir($targetPath) && !$force) {
                $skipped[] = $moduleName;
                continue;
            }

            // Extract files for this module
            $prefix = "src/Modules/{$moduleName}/";
            $extracted = false;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);

                if (str_starts_with($entryName, $prefix)) {
                    $relativePath = substr($entryName, strlen($prefix));

                    if (empty($relativePath)) {
                        continue;
                    }

                    $destFile = "{$targetPath}/{$relativePath}";
                    $destDir = dirname($destFile);

                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        file_put_contents($destFile, $content);
                        $extracted = true;
                    }
                }
            }

            if ($extracted) {
                $imported[] = $moduleName;
            }
        }

        $zip->close();

        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }
}
