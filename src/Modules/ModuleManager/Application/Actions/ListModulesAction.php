<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class ListModulesAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
    ) {}

    /**
     * @return array<int, array{name: string, display_name: string, description: string, depends: string[], extractable: bool, tags: string[], has_manifest: bool}>
     */
    public function execute(?string $tag = null): array
    {
        $modules = $this->registry->all();

        if ($tag) {
            $modules = array_filter($modules, fn ($m) => in_array($tag, $m->tags, true));
        }

        usort($modules, fn ($a, $b) => strcmp($a->name, $b->name));

        return array_map(fn ($m) => [
            'name' => $m->name,
            'display_name' => $m->displayName,
            'description' => $m->description,
            'depends' => $m->depends,
            'extractable' => $m->extractable,
            'tags' => $m->tags,
            'has_manifest' => $m->hasManifest,
        ], array_values($modules));
    }
}
