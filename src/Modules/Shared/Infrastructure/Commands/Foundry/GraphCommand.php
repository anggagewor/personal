<?php

namespace Modules\Shared\Infrastructure\Commands\Foundry;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class GraphCommand extends Command
{
    protected $signature = 'foundry:graph
                            {--format=mermaid : Output format (mermaid, table)}
                            {--output= : Write to file path}';

    protected $description = 'Generate module dependency graph';

    public function handle(): int
    {
        // Get scan results
        Artisan::call('foundry:scan', ['--json' => true]);
        $results = json_decode(Artisan::output(), true);

        if (empty($results)) {
            $this->error('No modules found.');
            return Command::FAILURE;
        }

        $format = $this->option('format');
        $output = match ($format) {
            'mermaid' => $this->generateMermaid($results),
            'table' => $this->generateTable($results),
            default => $this->generateMermaid($results),
        };

        if ($filePath = $this->option('output')) {
            File::ensureDirectoryExists(dirname(base_path($filePath)));
            File::put(base_path($filePath), $output);
            $this->info("Graph written to: {$filePath}");
        } else {
            $this->line($output);
        }

        return Command::SUCCESS;
    }

    private function generateMermaid(array $results): string
    {
        $lines = ['```mermaid', 'graph TD'];

        // Classify modules
        $standalone = [];
        $withDeps = [];

        foreach ($results as $module => $deps) {
            if (empty($deps)) {
                $standalone[] = $module;
            } else {
                $withDeps[$module] = $deps;
            }
        }

        // Add dependency edges
        foreach ($withDeps as $module => $deps) {
            foreach ($deps as $dep) {
                $lines[] = "    {$module} --> {$dep}";
            }
        }

        $lines[] = '';

        // Style standalone modules
        if (! empty($standalone)) {
            $lines[] = '    %% Standalone modules';
            foreach ($standalone as $module) {
                $lines[] = "    {$module}[{$module} ✓]";
            }
            $ids = implode(',', $standalone);
            $lines[] = "    style {$ids} fill:#d4edda,stroke:#28a745";
        }

        // Style Shared as foundation
        if (isset($results['Shared'])) {
            $lines[] = '    style Shared fill:#fff3cd,stroke:#ffc107';
        }

        $lines[] = '```';

        return implode("\n", $lines);
    }

    private function generateTable(array $results): string
    {
        $lines = [
            '| Module | Dependencies | Classification |',
            '|--------|-------------|----------------|',
        ];

        foreach ($results as $module => $deps) {
            $depList = empty($deps) ? '—' : implode(', ', $deps);
            $classification = $this->classify($module, $deps);
            $lines[] = "| {$module} | {$depList} | {$classification} |";
        }

        return implode("\n", $lines);
    }

    private function classify(string $module, array $deps): string
    {
        if (empty($deps)) {
            return '🟢 Standalone';
        }

        if ($deps === ['Shared']) {
            return '🟡 Shared only';
        }

        return '🔴 Bundle';
    }
}
