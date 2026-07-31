<?php

namespace Modules\Shared\Infrastructure\Commands\Foundry;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DoctorCommand extends Command
{
    protected $signature = 'foundry:doctor';

    protected $description = 'Overall health check of the module foundry';

    private string $modulesPath;

    private int $score = 100;

    private array $checks = [];

    public function handle(): int
    {
        $this->modulesPath = base_path('src/Modules');

        $this->newLine();
        $this->info('┌─────────────────────────────────────────────┐');
        $this->info('│            Foundry Doctor                   │');
        $this->info('└─────────────────────────────────────────────┘');
        $this->newLine();

        // Get dependency graph
        Artisan::call('foundry:scan', ['--json' => true]);
        $graph = json_decode(Artisan::output(), true);

        $totalModules = count($graph);

        // 1. Check circular dependencies
        $this->checkCircular($graph);

        // 2. Check all providers registered
        $this->checkProvidersRegistered($graph);

        // 3. Check DDD structure consistency
        $this->checkStructureConsistency($graph);

        // 4. Check manifest coverage
        $this->checkManifestCoverage($graph);

        // 5. Check standalone ratio
        $this->checkStandaloneRatio($graph);

        // 6. Check no App\ imports
        $this->checkNoAppImports();

        // 7. Check routes exist
        $this->checkRouteCoverage($graph);

        // Display results
        $this->displayResults($totalModules);

        return $this->score >= 70 ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkCircular(array $graph): void
    {
        $hasCycle = false;
        foreach (array_keys($graph) as $module) {
            $visited = [];
            $path = [];
            if ($this->hasCycle($module, $graph, $visited, $path)) {
                $hasCycle = true;
                break;
            }
        }

        if ($hasCycle) {
            $this->checks[] = ['✗', 'No circular dependencies', 'FAIL'];
            $this->score -= 20;
        } else {
            $this->checks[] = ['✓', 'No circular dependencies', 'PASS'];
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

    private function checkProvidersRegistered(array $graph): void
    {
        $providersFile = base_path('bootstrap/providers.php');
        $content = File::get($providersFile);

        $unregistered = [];
        foreach (array_keys($graph) as $module) {
            $providerClass = "Modules\\{$module}\\Infrastructure\\Providers\\";
            if (! str_contains($content, $providerClass)) {
                $unregistered[] = $module;
            }
        }

        if (empty($unregistered)) {
            $this->checks[] = ['✓', 'All providers registered', 'PASS'];
        } else {
            $count = count($unregistered);
            $this->checks[] = ['✗', "All providers registered ({$count} missing)", 'FAIL'];
            $this->score -= (5 * $count);
        }
    }

    private function checkStructureConsistency(array $graph): void
    {
        $inconsistent = [];

        foreach (array_keys($graph) as $module) {
            $modulePath = $this->modulesPath . '/' . $module;
            $layers = ['Domain', 'Application', 'Infrastructure'];

            foreach ($layers as $layer) {
                if (! is_dir($modulePath . '/' . $layer)) {
                    $inconsistent[] = "{$module}/{$layer}";
                }
            }
        }

        if (empty($inconsistent)) {
            $this->checks[] = ['✓', 'DDD 3-layer consistency (100%)', 'PASS'];
        } else {
            $count = count($inconsistent);
            $this->checks[] = ['!', "DDD 3-layer consistency ({$count} missing layers)", 'WARN'];
            $this->score -= (2 * $count);
        }
    }

    private function checkManifestCoverage(array $graph): void
    {
        $total = count($graph);
        $withManifest = 0;

        foreach (array_keys($graph) as $module) {
            if (file_exists($this->modulesPath . '/' . $module . '/module.json')) {
                $withManifest++;
            }
        }

        $percentage = $total > 0 ? round(($withManifest / $total) * 100) : 0;

        if ($percentage === 100) {
            $this->checks[] = ['✓', "Module manifests ({$percentage}%)", 'PASS'];
        } elseif ($percentage >= 50) {
            $this->checks[] = ['!', "Module manifests ({$withManifest}/{$total} = {$percentage}%)", 'WARN'];
            $this->score -= 5;
        } else {
            $this->checks[] = ['!', "Module manifests ({$withManifest}/{$total} = {$percentage}%)", 'WARN'];
            $this->score -= 10;
        }
    }

    private function checkStandaloneRatio(array $graph): void
    {
        $total = count($graph);
        $standalone = 0;
        $sharedOnly = 0;

        foreach ($graph as $deps) {
            if (empty($deps)) {
                $standalone++;
            } elseif ($deps === ['Shared']) {
                $sharedOnly++;
            }
        }

        $extractable = $standalone + $sharedOnly;
        $percentage = $total > 0 ? round(($extractable / $total) * 100) : 0;

        $this->checks[] = ['✓', "Extractable modules ({$extractable}/{$total} = {$percentage}%)", 'PASS'];
    }

    private function checkNoAppImports(): void
    {
        $phpFiles = File::allFiles($this->modulesPath);
        $violations = 0;

        foreach ($phpFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());
            if (preg_match('/use\s+App\\\\/', $content)) {
                $violations++;
            }
        }

        if ($violations === 0) {
            $this->checks[] = ['✓', 'No App\\ namespace imports in modules', 'PASS'];
        } else {
            $this->checks[] = ['✗', "No App\\ namespace imports ({$violations} violations)", 'FAIL'];
            $this->score -= (3 * $violations);
        }
    }

    private function checkRouteCoverage(array $graph): void
    {
        $total = count($graph);
        $withRoutes = 0;

        foreach (array_keys($graph) as $module) {
            $routesPath = $this->modulesPath . '/' . $module . '/Infrastructure/Routes';
            if (is_dir($routesPath) && ! empty(File::files($routesPath))) {
                $withRoutes++;
            }
        }

        $percentage = $total > 0 ? round(($withRoutes / $total) * 100) : 0;
        $this->checks[] = ['✓', "Modules with routes ({$withRoutes}/{$total} = {$percentage}%)", 'PASS'];
    }

    private function displayResults(int $totalModules): void
    {
        $this->line("  <fg=white>Foundry Health Report</>");
        $this->line("  Modules: {$totalModules}");
        $this->newLine();

        foreach ($this->checks as [$icon, $label, $status]) {
            $color = match ($status) {
                'PASS' => 'green',
                'WARN' => 'yellow',
                'FAIL' => 'red',
            };
            $this->line("    <fg={$color}>{$icon}</> {$label}");
        }

        $this->newLine();

        // Score display
        $scoreColor = match (true) {
            $this->score >= 90 => 'green',
            $this->score >= 70 => 'yellow',
            default => 'red',
        };

        $this->score = max(0, min(100, $this->score));
        $this->line("  <fg={$scoreColor}>Overall Health: {$this->score}/100</>");
        $this->newLine();
    }
}
