<?php

namespace Modules\LogReader\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LogReader\Application\Actions\ListLogFilesAction;
use Modules\LogReader\Application\Actions\ReadLogEntriesAction;
use Modules\LogReader\Infrastructure\Resources\LogEntryResource;

class LogReaderController extends Controller
{
    public function files(ListLogFilesAction $action): JsonResponse
    {
        $files = $action->execute();

        return response()->json([
            'data' => $files,
        ]);
    }

    public function entries(Request $request, ReadLogEntriesAction $action): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'string', 'regex:/^[\w\-\.]+\.log$/'],
            'per_page' => ['sometimes', 'integer', 'min:10', 'max:100'],
            'offset' => ['sometimes', 'integer', 'min:0'],
            'level' => ['sometimes', 'string'],
            'search' => ['sometimes', 'string', 'max:200'],
        ]);

        $result = $action->execute(
            filename: $request->query('file'),
            perPage: (int) $request->query('per_page', 30),
            offset: (int) $request->query('offset', 0),
            level: $request->query('level'),
            search: $request->query('search'),
        );

        return response()->json([
            'data' => LogEntryResource::collection($result['data']),
            'meta' => $result['meta'],
        ]);
    }
}
