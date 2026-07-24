<?php

namespace Modules\Quote\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Quote\Application\Actions\CreateQuoteAction;
use Modules\Quote\Application\Actions\DeleteQuoteAction;
use Modules\Quote\Application\Actions\GetTodayQuoteAction;
use Modules\Quote\Application\Actions\ListQuotesAction;
use Modules\Quote\Infrastructure\Requests\StoreQuoteRequest;

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

    public function store(StoreQuoteRequest $request, CreateQuoteAction $action): JsonResponse
    {
        $quote = $action->execute(
            content: $request->validated('content'),
            author: $request->validated('author'),
        );

        return response()->json([
            'data' => $quote,
            'message' => 'Quote berhasil ditambahkan.',
        ], 201);
    }

    public function destroy(int $id, DeleteQuoteAction $action): JsonResponse
    {
        $action->execute($id);

        return response()->json([
            'message' => 'Quote berhasil dihapus.',
        ]);
    }
}
