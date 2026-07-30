<?php

namespace Modules\Accounting\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Actions\LoadSampleEntriesAction;
use Modules\Accounting\Application\Actions\ResetAllDataAction;
use Modules\Accounting\Application\Actions\ResetJournalDataAction;

class ResetController extends Controller
{
    public function resetJournal(Request $request, ResetJournalDataAction $action): JsonResponse
    {
        if (!$request->input('confirm')) {
            return response()->json(['message' => 'Konfirmasi diperlukan.'], 422);
        }

        $count = $action->execute($request->user()->id);

        return response()->json([
            'message' => "Berhasil menghapus {$count} jurnal.",
            'count' => $count,
        ]);
    }

    public function resetAll(Request $request, ResetAllDataAction $action): JsonResponse
    {
        if (!$request->input('confirm')) {
            return response()->json(['message' => 'Konfirmasi diperlukan.'], 422);
        }

        $action->execute($request->user()->id);

        return response()->json([
            'message' => 'Semua data akuntansi berhasil direset.',
        ]);
    }

    public function loadSample(Request $request, LoadSampleEntriesAction $action): JsonResponse
    {
        $count = $action->execute($request->user()->id);

        return response()->json([
            'message' => "Berhasil memuat {$count} jurnal contoh.",
            'count' => $count,
        ]);
    }
}
