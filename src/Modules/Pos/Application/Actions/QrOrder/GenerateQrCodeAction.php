<?php

namespace Modules\Pos\Application\Actions\QrOrder;

use Modules\Pos\Domain\Contracts\TableRepositoryInterface;

class GenerateQrCodeAction
{
    public function __construct(
        private TableRepositoryInterface $tableRepository,
    ) {}

    /**
     * Generate a QR code URL for a table.
     * Returns the public URL that customers scan to access the menu.
     *
     * @throws \RuntimeException If table not found.
     */
    public function execute(int $tableId): string
    {
        $table = $this->tableRepository->findById($tableId);

        if ($table === null) {
            throw new \RuntimeException('Meja tidak ditemukan.');
        }

        return "/pos/qr/{$table->token}/menu";
    }
}
