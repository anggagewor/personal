<?php

namespace Modules\ModuleManager\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ModuleManager\Application\Actions\ExtractModuleAction;
use Modules\ModuleManager\Application\Actions\ImportModuleAction;
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

        return response()->json([
            'data' => [
                'name' => $module->name,
                'display_name' => $module->displayName,
                'description' => $module->description,
                'depends' => $module->depends,
                'dependency_tree' => $deps,
                'extractable' => $module->extractable,
                'tags' => $module->tags,
                'has_manifest' => $module->hasManifest,
            ],
        ]);
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

            // Cleanup uploaded archive
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
