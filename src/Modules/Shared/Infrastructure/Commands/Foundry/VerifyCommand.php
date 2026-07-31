<?php

namespace Modules\Shared\Infrastructure\Commands\Foundry;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class VerifyCommand extends Command
{
    protected $signature = 'foundry:verify
                            {module? : Specific module to verify (or all)}
                            {--strict : Fail on warnings too}';

    protected $description = 'Verify module integrity and architectural rules';

    private string $modulesPath;

    private array $errors = [];

    private array $warnings = [];

    public function handle(): int
    {
        $this->modulesPath = base_path('src/Modules');
        $targetModule = $this->argument('module');

        $modules = $targetModule
            ? [$targetModule]
            : $this->getModuleNames();

        $this->newLine();
        $this->info('┌─────────────────────────────────────────────┐');
        $this->info('│         Foundry Module Verifier             │');
        $this->info('└─────────────────────────────────────────────┘');
        $this->newLine();

        // Get dependency graph
        Artisan::call('foundry:scan', ['--json' => true]);
        $dependencyGraph = json_decode(Artisan::output(), true);

        foreach ($modules as $module) {
            $this->verifyModule($module, $dependencyGraph);
        }

        // Check circular dependencies across all
        $this->checkCircularDependencies($dependencyGraph);

        // Check illegal dependencies (from manifest rules)
        $this->checkIllegalDependencies($dependencyGraph);

        // Output results
        $this->displayResults();

        if (! empty($this->errors)) {
            return Command::FAILURE;
        }

        if ($this->option('strict') && ! empty($this->warnings)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function getModuleNames(): array
    {
        return collect(File::directories($this->modulesPath))
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values()
            ->all();
    }

    private function verifyModule(string $module, array $graph): void
    {
        $modulePath = $this->modulesPath . '/' . $module;

        if (! is_dir($modulePath)) {
            $this->errors[] = "[{$module}] Module directory not found";
            return;
        }

        $this->line("  Checking <fg=white>{$module}</>...");

        // 1. Check structure (DDD layers)
        $this->checkStructure($module, $modulePath);

        // 2. Check ServiceProvider exists
        $this->checkProvider($module, $modulePath);

        // 3. Check routes exist
        $this->checkRoutes($module, $modulePath);

        // 4. Check manifest exists
        $this->checkManifest($module, $modulePath);

        // 5. Verify declared dependencies match actual
        $this->checkManifestDependencies($module, $modulePath, $graph);
    }

    private function checkStructure(string $module, string $modulePath): void
    {
        $requiredLayers = ['Domain', 'Application', 'Infrastructure'];

        foreach ($requiredLayers as $layer) {
            if (! is_dir($modulePath . '/' . $layer)) {
                $this->errors[] = "[{$module}] Missing DDD layer: {$layer}/";
            }
        }
    }

    private function checkProvider(string $module, string $modulePath): void
    {
        $providerPath = $modulePath . '/Infrastructure/Providers';

        if (! is_dir($providerPath)) {
            $this->errors[] = "[{$module}] Missing Providers directory";
            return;
        }

        $providers = File::files($providerPath);
        if (empty($providers)) {
            $this->errors[] = "[{$module}] No ServiceProvider found";
        }
    }

    private function checkRoutes(string $module, string $modulePath): void
    {
        $routesPath = $modulePath . '/Infrastructure/Routes';

        if (! is_dir($routesPath)) {
            $this->warnings[] = "[{$module}] No Routes directory (module has no API endpoints)";
        }
    }

    private function checkManifest(string $module, string $modulePath): void
    {
        $manifestPath = $modulePath . '/module.json';

        if (! file_exists($manifestPath)) {
            $this->warnings[] = "[{$module}] Missing module.json manifest";
        }
    }

    private function checkManifestDependencies(string $module, string $modulePath, array $graph): void
    {
        $manifestPath = $modulePath . '/module.json';

        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(File::get($manifestPath), true);
        $declared = $manifest['depends'] ?? [];
        $actual = $graph[$module] ?? [];

        // Check for undeclared dependencies
        $undeclared = array_diff($actual, $declared);
        foreach ($undeclared as $dep) {
            $this->errors[] = "[{$module}] Undeclared dependency: {$dep} (found in code but not in module.json)";
        }

        // Check for stale declarations
        $stale = array_diff($declared, $actual);
        foreach ($stale as $dep) {
            $this->warnings[] = "[{$module}] Stale dependency: {$dep} (declared in module.json but not found in code)";
        }
    }

    private function checkCircularDependencies(array $graph): void
    {
        foreach (array_keys($graph) as $module) {
            $visited = [];
            $path = [];

            if ($this->hasCycle($module, $graph, $visited, $path)) {
                $cycle = implode(' → ', $path) . " → {$module}";
                $this->errors[] = "[CIRCULAR] {$cycle}";
            }
        }
    }

    private function hasCycle(string $module, array $graph, array &$visited, array &$path): bool
    {
        if (in_array($module, $path)) {
            return true;
        }

        if (in_array($module, $visited)) {
            return false;
        }

        $visited[] = $module;
        $path[] = $module;

        foreach ($graph[$module] ?? [] as $dep) {
            if ($this->hasCycle($dep, $graph, $visited, $path)) {
                return true;
            }
        }

        array_pop($path);

        return false;
    }

    private function checkIllegalDependencies(array $graph): void
    {
        // Rule: Shared must not depend on any other module
        if (! empty($graph['Shared'] ?? [])) {
            $deps = implode(', ', $graph['Shared']);
            $this->errors[] = "[Shared] Foundation module has dependencies: {$deps} (Shared must have zero dependencies)";
        }

        // Rule: Modules must not depend on App\ namespace
        $phpFiles = File::allFiles($this->modulesPath);
        foreach ($phpFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());
            if (preg_match('/use\s+App\\\\/', $content)) {
                $relativePath = str_replace(base_path() . '/', '', $file->getPathname());
                $this->errors[] = "[ILLEGAL] {$relativePath} imports from App\\ namespace";
            }
        }
    }

    private function displayResults(): void
    {
        $this->newLine();

        if (! empty($this->errors)) {
            $this->error('  ERRORS (' . count($this->errors) . ')');
            foreach ($this->errors as $error) {
                $this->line("    <fg=red>✗</> {$error}");
            }
            $this->newLine();
        }

        if (! empty($this->warnings)) {
            $this->warn('  WARNINGS (' . count($this->warnings) . ')');
            foreach ($this->warnings as $warning) {
                $this->line("    <fg=yellow>!</> {$warning}");
            }
            $this->newLine();
        }

        if (empty($this->errors) && empty($this->warnings)) {
            $this->info('  ✓ All checks passed!');
            $this->newLine();
        } elseif (empty($this->errors)) {
            $this->info('  ✓ No errors (warnings only)');
            $this->newLine();
        } else {
            $this->error('  ✗ Verification failed');
            $this->newLine();
        }
    }
}
