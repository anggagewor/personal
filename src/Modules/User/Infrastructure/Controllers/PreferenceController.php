<?php

namespace Modules\User\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Application\Actions\UpdatePreferencesAction;

class PreferenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $user->getPreferencesWithDefaults(),
        ]);
    }

    public function update(Request $request, UpdatePreferencesAction $action): JsonResponse
    {
        $validated = $request->validate([
            'theme' => ['sometimes', 'string', 'in:light,dark,system'],
            'primary_color' => ['sometimes', 'string', 'in:indigo,blue,emerald,rose,amber,teal,violet,slate'],
            'locale' => ['sometimes', 'string', 'in:id,en'],
            'sidebar_collapsed' => ['sometimes', 'boolean'],
            'timezones' => ['sometimes', 'array', 'max:10'],
            'timezones.*.label' => ['required_with:timezones', 'string', 'max:50'],
            'timezones.*.timezone' => ['required_with:timezones', 'string', 'timezone:all'],
        ]);

        $preferences = $action->execute($request->user()->id, $validated);

        return response()->json([
            'data' => $preferences,
            'message' => 'Preferensi berhasil disimpan.',
        ]);
    }
}
