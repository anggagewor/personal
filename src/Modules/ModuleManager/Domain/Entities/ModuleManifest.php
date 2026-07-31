<?php

namespace Modules\ModuleManager\Domain\Entities;

class ModuleManifest
{
    public function __construct(
        public string $name,
        public string $displayName,
        public string $description,
        public array $depends,
        public bool $extractable,
        public array $tags,
        public string $path,
        public bool $hasManifest,
    ) {}

    /**
     * Resolve full dependency tree (including transitive dependencies).
     *
     * @param array<string, ModuleManifest> $registry All modules indexed by name
     * @return string[] Ordered list of dependency names (leaves first)
     */
    public function resolveDependencyTree(array $registry): array
    {
        $resolved = [];
        $this->resolve($this->name, $registry, $resolved, []);

        return $resolved;
    }

    private function resolve(string $name, array $registry, array &$resolved, array $seen): void
    {
        if (in_array($name, $resolved, true)) {
            return;
        }

        if (in_array($name, $seen, true)) {
            return; // circular — skip
        }

        $seen[] = $name;

        if (isset($registry[$name])) {
            foreach ($registry[$name]->depends as $dep) {
                $this->resolve($dep, $registry, $resolved, $seen);
            }
        }

        if ($name !== $this->name) {
            $resolved[] = $name;
        }
    }
}
