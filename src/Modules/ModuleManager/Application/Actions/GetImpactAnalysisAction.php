<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class GetImpactAnalysisAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
    ) {}

    /**
     * Calculate impact: which modules are affected if the given module changes.
     * Traverses reverse dependency graph recursively.
     */
    public function execute(string $moduleName): array
    {
        $module = $this->registry->find($moduleName);
        if (!$module) {
            return [];
        }

        // Build reverse dependency map
        $reverseDeps = [];
        foreach ($this->registry->all() as $m) {
            foreach ($m->depends as $dep) {
                $reverseDeps[$dep][] = $m->name;
            }
        }

        // BFS to find all affected modules
        $affected = [];
        $queue = [$moduleName];
        $visited = [$moduleName];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $dependents = $reverseDeps[$current] ?? [];

            foreach ($dependents as $dependent) {
                if (!in_array($dependent, $visited, true)) {
                    $visited[] = $dependent;
                    $affected[] = [
                        'name' => $dependent,
                        'reason' => $current === $moduleName ? 'direct' : "via {$current}",
                    ];
                    $queue[] = $dependent;
                }
            }
        }

        return [
            'module' => $moduleName,
            'affected_count' => count($affected),
            'affected' => $affected,
        ];
    }
}
