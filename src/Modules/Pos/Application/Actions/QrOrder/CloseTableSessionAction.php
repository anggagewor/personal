<?php

namespace Modules\Pos\Application\Actions\QrOrder;

use Modules\Pos\Domain\Contracts\TableRepositoryInterface;

class CloseTableSessionAction
{
    public function __construct(
        private TableRepositoryInterface $tableRepository,
    ) {}

    /**
     * Close the active table session.
     * Frees the table for new customers.
     *
     * @throws \RuntimeException If table not found or no active session exists.
     */
    public function execute(int $tableId): void
    {
        $table = $this->tableRepository->findById($tableId);

        if ($table === null) {
            throw new \RuntimeException('Meja tidak ditemukan.');
        }

        $session = $this->tableRepository->findActiveSession($tableId);

        if ($session === null) {
            throw new \RuntimeException('Tidak ada sesi aktif untuk meja ini.');
        }

        $this->tableRepository->closeSession($session->id);
    }
}
