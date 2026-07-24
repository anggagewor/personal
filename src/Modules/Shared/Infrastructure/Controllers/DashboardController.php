<?php

namespace Modules\Shared\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Shared\Application\Actions\GetWeeklySummaryAction;

class DashboardController extends Controller
{
    public function weeklySummary(Request $request, GetWeeklySummaryAction $action): JsonResponse
    {
        $data = $action->execute($request->user()->id);

        return response()->json([
            'data' => $data,
        ]);
    }
}
