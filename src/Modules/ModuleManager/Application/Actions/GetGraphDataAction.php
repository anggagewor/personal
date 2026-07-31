<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class GetGraphDataAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
    ) {}

    /**
     * Return graph data: nodes + edges for visualization.
     */
    public function execute(): array
    {
        $modules = $this->registry->all();
        $nodes = [];
        $edges = [];
        $usedBy = []; // reverse deps

        foreach ($modules as $module) {
            $nodes[] = [
                'id' => $module->name,
                'label' => $module->displayName,
                'tags' => $module->tags,
                'extractable' => $module->extractable,
                'standalone' => empty($module->depends),
                'dep_count' => count($module->depends),
            ];

            foreach ($module->depends as $dep) {
                $edges[] = [
                    'from' => $module->name,
                    'to' => $dep,
                ];
                $usedBy[$dep][] = $module->name;
            }
        }

        // Enrich nodes with usedBy count
        foreach ($nodes as &$node) {
            $node['used_by_count'] = count($usedBy[$node['id']] ?? []);
            $node['used_by'] = $usedBy[$node['id']] ?? [];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }
}
