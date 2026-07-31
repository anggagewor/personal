<?php

namespace Modules\ModuleManager\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ModuleManager\Application\Actions\ExtractModuleAction;
use Modules\ModuleManager\Application\Actions\GetExtractPreviewAction;
use Modules\ModuleManager\Application\Actions\GetGraphDataAction;
use Modules\ModuleManager\Application\Actions\GetHealthScoreAction;
use Modules\ModuleManager\Application\Actions\GetImpactAnalysisAction;
use Modules\ModuleManager\Application\Actions\ImportModuleAction;
use Modules\ModuleManager\Application\Actions\InspectModuleAction;
use Modules\ModuleManager\Application\Actions\ListModulesAction;
use Modules\ModuleManager\Domain\Contracts\ModuleRegistryInterface;

class ModuleManagerController extends Controller
{
    public function index(Request $request, ListModulesAction $action): JsonResponse
    {
        $tag = $request->query('tag');
        $modules = $action->execute($tag);

        return response()->json(['data' => $modules]);
    }

    public function show(string $name, ModuleRegistryInterface $registry): JsonResponse
    {
        $module = $registry->find($name);

        if (!$module) {
            return response()->json(['message' => "Module '{$name}' tidak ditemukan."], 404);
        }

        $allModules = [];
        foreach ($registry->all() as $m) {
            $allModules[$m->name] = $m;
        }

        $deps = $module->resolveDependencyTree($allModules);

        // Reverse deps (used by)
        $usedBy = [];
        foreach ($registry->all() as $m) {
            if (in_array($module->name, $m->depends, true)) {
                $usedBy[] = $m->name;
            }
        }

        return response()->json([
            'data' => [
                'name' => $module->name,
                'display_name' => $module->displayName,
                'description' => $module->description,
                'depends' => $module->depends,
                'dependency_tree' => $deps,
                'used_by' => $usedBy,
                'extractable' => $module->extractable,
                'tags' => $module->tags,
                'has_manifest' => $module->hasManifest,
            ],
        ]);
    }

    public function graph(GetGraphDataAction $action): JsonResponse
    {
        return response()->json(['data' => $action->execute()]);
    }

    public function inspect(string $name, InspectModuleAction $action): JsonResponse
    {
        $result = $action->execute($name);

        if (!$result) {
            return response()->json(['message' => "Module '{$name}' tidak ditemukan."], 404);
        }

        return response()->json(['data' => $result]);
    }

    public function health(Request $request, GetHealthScoreAction $action): JsonResponse
    {
        $moduleName = $request->query('module');
        $result = $action->execute($moduleName);

        return response()->json(['data' => $result]);
    }

    public function impact(string $name, GetImpactAnalysisAction $action): JsonResponse
    {
        $result = $action->execute($name);

        return response()->json(['data' => $result]);
    }

    public function extractPreview(string $name, Request $request, GetExtractPreviewAction $action): JsonResponse
    {
        $includeDeps = $request->boolean('include_dependencies', true);
        $result = $action->execute($name, $includeDeps);

        if (!$result) {
            return response()->json(['message' => "Module '{$name}' tidak ditemukan."], 404);
        }

        return response()->json(['data' => $result]);
    }

    public function extract(string $name, Request $request, ExtractModuleAction $action): JsonResponse
    {
        $includeDeps = $request->boolean('include_dependencies', true);

        try {
            $archivePath = $action->execute($name, $includeDeps);

            return response()->json([
                'data' => [
                    'module' => $name,
                    'archive_path' => $archivePath,
                    'include_dependencies' => $includeDeps,
                ],
                'message' => 'Module berhasil di-extract.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function import(Request $request, ImportModuleAction $action): JsonResponse
    {
        $request->validate([
            'archive' => 'required|file|mimes:zip',
            'force' => 'sometimes|boolean',
        ]);

        $file = $request->file('archive');
        $archivePath = $file->store('module-imports', 'local');
        $fullPath = storage_path("app/{$archivePath}");

        $force = $request->boolean('force', false);

        try {
            $result = $action->execute($fullPath, $force);

            @unlink($fullPath);

            return response()->json([
                'data' => $result,
                'message' => 'Module berhasil di-import.',
            ]);
        } catch (\RuntimeException $e) {
            @unlink($fullPath);

            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
