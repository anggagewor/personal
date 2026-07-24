<?php

namespace Modules\Quote\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quote\Application\Actions\GetTodayQuoteAction;
use Modules\Quote\Application\Actions\ListQuotesAction;

class QuoteController extends Controller
{
    public function index(Request $request, ListQuotesAction $action): JsonResponse
    {
        $result = $action->execute(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 10),
            search: $request->query('search'),
        );

        return response()->json([
            'data' => $result['items'],
            'meta' => [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
            ],
        ]);
    }

    public function today(GetTodayQuoteAction $action): JsonResponse
    {
        $quote = $action->execute();

        return response()->json([
            'data' => $quote,
        ]);
    }
}
