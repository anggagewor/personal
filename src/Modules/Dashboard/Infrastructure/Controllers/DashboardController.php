<?php

namespace Modules\Dashboard\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Dashboard\Application\Actions\GetWeeklySummaryAction;

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
