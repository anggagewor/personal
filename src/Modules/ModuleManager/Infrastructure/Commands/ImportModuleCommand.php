<?php

namespace Modules\ModuleManager\Infrastructure\Commands;

use Illuminate\Console\Command;
use Modules\ModuleManager\Application\Actions\ImportModuleAction;

class ImportModuleCommand extends Command
{
    protected $signature = 'foundry:import
        {path : Path to the module archive (.zip)}
        {--force : Overwrite existing modules}';

    protected $description = 'Import a module from a zip archive into the project';

    public function handle(ImportModuleAction $action): int
    {
        $archivePath = $this->argument('path');
        $force = $this->option('force');

        if (!file_exists($archivePath)) {
            $this->error("  Archive not found: {$archivePath}");

            return self::FAILURE;
        }

        $this->info("  Importing from: {$archivePath}");

        if ($force) {
            $this->warn("  --force enabled: existing modules will be overwritten.");
        }

        try {
            $result = $action->execute($archivePath, $force);

            $this->newLine();

            if (!empty($result['imported'])) {
                $this->info('  ✓ Imported:');
                foreach ($result['imported'] as $name) {
                    $this->line("    - {$name}");
                }
            }

            if (!empty($result['skipped'])) {
                $this->warn('  ⚠ Skipped (already exists, use --force to overwrite):');
                foreach ($result['skipped'] as $name) {
                    $this->line("    - {$name}");
                }
            }

            $this->newLine();
            $this->info('  Next steps:');
            $this->line('    1. Register ServiceProvider(s) in bootstrap/providers.php');
            $this->line('    2. Run: php artisan migrate');
            $this->line('    3. Run: composer dump-autoload');
            $this->newLine();

            return self::SUCCESS;
        } catch (\RuntimeException $e) {
            $this->error("  ✗ {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
