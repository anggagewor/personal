<?php

namespace Modules\ModuleManager\Infrastructure\Commands;

use Illuminate\Console\Command;
use Modules\ModuleManager\Application\Actions\ListModulesAction;

class ListModulesCommand extends Command
{
    protected $signature = 'foundry:list {--tag= : Filter by tag}';

    protected $description = 'List all registered modules with their dependencies and status';

    public function handle(ListModulesAction $action): int
    {
        $tag = $this->option('tag');
        $modules = $action->execute($tag);

        if (empty($modules)) {
            $this->info('No modules found.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('  <fg=cyan>┌─────────────────────────────────────────────┐</>');
        $this->line('  <fg=cyan>│         Foundry Module Registry             │</>');
        $this->line('  <fg=cyan>└─────────────────────────────────────────────┘</>');
        $this->newLine();

        $rows = [];
        foreach ($modules as $m) {
            $deps = empty($m['depends']) ? '—' : implode(', ', $m['depends']);
            $status = $m['extractable'] ? '<fg=green>extractable</>' : '<fg=yellow>bundled</>';
            $tags = implode(', ', $m['tags']);

            $rows[] = [$m['name'], $deps, $status, $tags];
        }

        $this->table(
            ['Module', 'Dependencies', 'Status', 'Tags'],
            $rows
        );

        $this->newLine();
        $this->info("  Total: " . count($modules) . " modules");
        $this->newLine();

        return self::SUCCESS;
    }
}
