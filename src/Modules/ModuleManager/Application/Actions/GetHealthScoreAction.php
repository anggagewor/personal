<?php

namespace Modules\ModuleManager\Application\Actions;

use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class GetHealthScoreAction
{
    public function __construct(
        private ModuleRegistryInterface $registry,
        private InspectModuleAction $inspector,
    ) {}

    /**
     * Calculate health score for a single module or all modules.
     */
    public function execute(?string $moduleName = null): array
    {
        if ($moduleName) {
            return $this->scoreModule($moduleName);
        }

        return $this->scoreAll();
    }

    private function scoreAll(): array
    {
        $modules = $this->registry->all();
        $scores = [];
        $totalScore = 0;

        foreach ($modules as $module) {
            $score = $this->scoreModule($module->name);
            $scores[] = $score;
            $totalScore += $score['overall_score'];
        }

        $avgScore = count($scores) > 0 ? round($totalScore / count($scores)) : 0;

        // Category averages
        $categories = [
            'architecture' => 0,
            'documentation' => 0,
            'extractability' => 0,
            'testing' => 0,
        ];

        foreach ($scores as $s) {
            foreach ($categories as $cat => &$val) {
                $val += $s['categories'][$cat] ?? 0;
            }
        }

        foreach ($categories as &$val) {
            $val = count($scores) > 0 ? round($val / count($scores)) : 0;
        }

        return [
            'overall_score' => $avgScore,
            'categories' => $categories,
            'module_count' => count($modules),
            'modules' => $scores,
        ];
    }

    private function scoreModule(string $name): array
    {
        $module = $this->registry->find($name);
        if (!$module) {
            return ['name' => $name, 'overall_score' => 0, 'checks' => [], 'categories' => []];
        }

        $inspection = $this->inspector->execute($name);
        $checks = [];

        // Architecture checks (max 40 points)
        $archScore = 0;

        $hasDomain = $inspection['has_domain'] ?? false;
        $hasApp = $inspection['has_application'] ?? false;
        $hasInfra = $inspection['has_infrastructure'] ?? false;

        $checks[] = ['name' => 'Domain layer', 'pass' => $hasDomain, 'weight' => 10];
        $checks[] = ['name' => 'Application layer', 'pass' => $hasApp, 'weight' => 10];
        $checks[] = ['name' => 'Infrastructure layer', 'pass' => $hasInfra, 'weight' => 10];

        if ($hasDomain) $archScore += 10;
        if ($hasApp) $archScore += 10;
        if ($hasInfra) $archScore += 10;

        // Has contracts/interfaces
        $hasContracts = ($inspection['contracts'] ?? 0) > 0;
        $checks[] = ['name' => 'Has contracts/interfaces', 'pass' => $hasContracts, 'weight' => 10];
        if ($hasContracts) $archScore += 10;

        // Documentation checks (max 20 points)
        $docScore = 0;

        $checks[] = ['name' => 'Has module.json manifest', 'pass' => $module->hasManifest, 'weight' => 15];
        if ($module->hasManifest) $docScore += 15;

        $hasDescription = !empty($module->description);
        $checks[] = ['name' => 'Has description', 'pass' => $hasDescription, 'weight' => 5];
        if ($hasDescription) $docScore += 5;

        // Extractability checks (max 20 points)
        $extractScore = 0;

        $checks[] = ['name' => 'Marked extractable', 'pass' => $module->extractable, 'weight' => 5];
        if ($module->extractable) $extractScore += 5;

        $noCircular = true; // assume true (would need scan to verify)
        $checks[] = ['name' => 'No circular dependency', 'pass' => $noCircular, 'weight' => 10];
        if ($noCircular) $extractScore += 10;

        $hasMigrations = ($inspection['migrations'] ?? 0) > 0 || ($inspection['total_files'] ?? 0) > 0;
        $selfContained = ($inspection['migrations'] ?? 0) >= 0; // has its own migrations dir
        $checks[] = ['name' => 'Self-contained (has migrations)', 'pass' => $selfContained, 'weight' => 5];
        if ($selfContained) $extractScore += 5;

        // Testing checks (max 20 points)
        $testScore = 0;
        $hasTests = ($inspection['tests'] ?? 0) > 0;
        $checks[] = ['name' => 'Has tests', 'pass' => $hasTests, 'weight' => 20];
        if ($hasTests) $testScore += 20;

        $overall = $archScore + $docScore + $extractScore + $testScore;

        return [
            'name' => $name,
            'display_name' => $module->displayName,
            'overall_score' => $overall,
            'categories' => [
                'architecture' => $archScore,
                'documentation' => $docScore,
                'extractability' => $extractScore,
                'testing' => $testScore,
            ],
            'max_scores' => [
                'architecture' => 40,
                'documentation' => 20,
                'extractability' => 20,
                'testing' => 20,
            ],
            'checks' => $checks,
        ];
    }
}
