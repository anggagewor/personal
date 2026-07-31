<?php

namespace Modules\ModuleManager\Infrastructure\Commands;

use Illuminate\Console\Command;
use Modules\ModuleManager\Application\Actions\ExtractModuleAction;
use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class ExtractModuleCommand extends Command
{
    protected $signature = 'foundry:extract
        {module : Module name to extract}
        {--no-deps : Do not include dependencies}';

    protected $description = 'Extract a module (and its dependencies) into a zip archive';

    public function handle(ExtractModuleAction $action, ModuleRegistryInterface $registry): int
    {
        $moduleName = $this->argument('module');
        $includeDeps = !$this->option('no-deps');

        $module = $registry->find($moduleName);

        if (!$module) {
            $this->error("  Module '{$moduleName}' not found.");

            return self::FAILURE;
        }

        if (!$module->extractable) {
            $this->error("  Module '{$moduleName}' is marked as not extractable.");

            return self::FAILURE;
        }

        $this->info("  Extracting module: {$moduleName}");

        if ($includeDeps && !empty($module->depends)) {
            $allModules = [];
            foreach ($registry->all() as $m) {
                $allModules[$m->name] = $m;
            }

            $deps = $module->resolveDependencyTree($allModules);
            $this->line("  Including dependencies: " . implode(', ', $deps));
        }

        try {
            $archivePath = $action->execute($moduleName, $includeDeps);
            $this->newLine();
            $this->info("  ✓ Archive created: {$archivePath}");
            $this->newLine();

            return self::SUCCESS;
        } catch (\RuntimeException $e) {
            $this->error("  ✗ {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
