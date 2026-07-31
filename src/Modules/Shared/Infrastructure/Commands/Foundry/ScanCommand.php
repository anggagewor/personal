<?php

namespace Modules\Shared\Infrastructure\Commands\Foundry;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScanCommand extends Command
{
    protected $signature = 'foundry:scan
                            {module? : Specific module to scan}
                            {--json : Output as JSON}';

    protected $description = 'Scan module dependencies from source code';

    private string $modulesPath;

    public function handle(): int
    {
        $this->modulesPath = base_path('src/Modules');
        $targetModule = $this->argument('module');

        $modules = $targetModule
            ? [$targetModule]
            : $this->getModuleNames();

        $results = [];

        foreach ($modules as $module) {
            $modulePath = $this->modulesPath . '/' . $module;

            if (! is_dir($modulePath)) {
                $this->error("Module '{$module}' not found.");
                return Command::FAILURE;
            }

            $dependencies = $this->scanModuleDependencies($module, $modulePath);
            $results[$module] = $dependencies;
        }

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        }

        $this->displayResults($results);

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

    private function scanModuleDependencies(string $module, string $modulePath): array
    {
        $phpFiles = File::allFiles($modulePath);
        $dependencies = [];

        foreach ($phpFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());
            $fileDeps = $this->extractDependencies($content, $module);

            foreach ($fileDeps as $dep) {
                if (! in_array($dep, $dependencies)) {
                    $dependencies[] = $dep;
                }
            }
        }

        sort($dependencies);

        return $dependencies;
    }

    private function extractDependencies(string $content, string $currentModule): array
    {
        $dependencies = [];

        // Strip comments to avoid false positives
        $content = $this->stripComments($content);

        // Match: use Modules\XYZ\...
        preg_match_all('/use\s+Modules\\\\([A-Za-z]+)\\\\/', $content, $matches);

        foreach ($matches[1] as $referencedModule) {
            if ($referencedModule !== $currentModule && ! in_array($referencedModule, $dependencies)) {
                $dependencies[] = $referencedModule;
            }
        }

        // Match: \Modules\XYZ\... inline (FQCN without use statement)
        preg_match_all('/\\\\Modules\\\\([A-Za-z]+)\\\\/', $content, $inlineMatches);

        foreach ($inlineMatches[1] as $referencedModule) {
            if ($referencedModule !== $currentModule && ! in_array($referencedModule, $dependencies)) {
                $dependencies[] = $referencedModule;
            }
        }

        return $dependencies;
    }

    private function stripComments(string $content): string
    {
        // Remove single-line comments (// ...)
        $content = preg_replace('#//.*$#m', '', $content);

        // Remove multi-line comments (/* ... */)
        $content = preg_replace('#/\*.*?\*/#s', '', $content);

        // Remove doc-block comments (/** ... */)
        $content = preg_replace('#/\*\*.*?\*/#s', '', $content);

        return $content;
    }

    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('┌─────────────────────────────────────────────┐');
        $this->info('│         Foundry Dependency Scanner          │');
        $this->info('└─────────────────────────────────────────────┘');
        $this->newLine();

        $standalone = [];
        $withDeps = [];

        foreach ($results as $module => $deps) {
            if (empty($deps)) {
                $standalone[] = $module;
            } else {
                $withDeps[$module] = $deps;
            }
        }

        // Modules with dependencies
        foreach ($withDeps as $module => $deps) {
            $depList = implode(', ', $deps);
            $this->line("  <fg=white>{$module}</> → <fg=yellow>{$depList}</>");
        }

        $this->newLine();

        // Standalone modules
        if (! empty($standalone)) {
            $this->info('  Standalone (no dependencies):');
            foreach ($standalone as $module) {
                $this->line("    <fg=green>✓</> {$module}");
            }
        }

        $this->newLine();
        $this->line("  Total: <fg=white>" . count($results) . "</> modules | <fg=green>" . count($standalone) . "</> standalone | <fg=yellow>" . count($withDeps) . '</> with deps');
        $this->newLine();
    }
}
