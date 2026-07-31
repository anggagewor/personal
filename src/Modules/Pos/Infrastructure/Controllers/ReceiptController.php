<?php

namespace Modules\Pos\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Controllers\BaseController;
use Modules\Pos\Application\Actions\Transaction\GenerateReceiptAction;
use Modules\Pos\Domain\Contracts\OutletRepositoryInterface;
use Modules\Pos\Domain\Contracts\TransactionRepositoryInterface;
use Modules\Shared\Infrastructure\Traits\AuthorizesOwnership;

class ReceiptController extends BaseController
{
    use AuthorizesOwnership;

    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private OutletRepositoryInterface $outletRepository,
    ) {}

    public function show(Request $request, int $id, GenerateReceiptAction $action): JsonResponse
    {
        $transaction = $this->transactionRepository->findById($id);

        if (! $transaction) {
            abort(404, 'Transaksi tidak ditemukan.');
        }

        $this->findOwnedOrFail($this->outletRepository, $transaction->outletId, $request);

        $receipt = $action->execute($id);

        return response()->json([
            'data' => $receipt,
            'message' => 'Struk berhasil dibuat.',
        ]);
    }

    public function updateTemplate(Request $request, int $outletId): JsonResponse
    {
        $outlet = $this->findOwnedOrFail($this->outletRepository, $outletId, $request);

        $validated = $request->validate([
            'receipt_header' => ['nullable', 'string', 'max:500'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'receipt_width' => ['nullable', 'string', 'in:58mm,80mm'],
        ]);

        $settings = $outlet->settings ?? [];
        $settings = array_merge($settings, $validated);

        $outletModel = \Modules\Pos\Infrastructure\Models\OutletModel::find($outletId);
        $outletModel->update(['settings' => $settings]);

        return response()->json([
            'data' => $settings,
            'message' => 'Template struk berhasil diperbarui.',
        ]);
    }
}
