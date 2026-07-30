<?php

namespace Modules\Accounting\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Actions\GetBalanceSheetAction;
use Modules\Accounting\Application\Actions\GetIncomeStatementAction;
use Modules\Accounting\Application\Actions\GetTrialBalanceAction;

class ReportController extends Controller
{
    public function trialBalance(Request $request, GetTrialBalanceAction $action): JsonResponse
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-t'));

        $result = $action->execute($request->user()->id, $startDate, $endDate);

        return response()->json(['data' => $result]);
    }

    public function incomeStatement(Request $request, GetIncomeStatementAction $action): JsonResponse
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-t'));

        $result = $action->execute($request->user()->id, $startDate, $endDate);

        return response()->json(['data' => $result]);
    }

    public function balanceSheet(Request $request, GetBalanceSheetAction $action): JsonResponse
    {
        $asOfDate = $request->query('date', date('Y-m-d'));

        $result = $action->execute($request->user()->id, $asOfDate);

        return response()->json(['data' => $result]);
    }
}
