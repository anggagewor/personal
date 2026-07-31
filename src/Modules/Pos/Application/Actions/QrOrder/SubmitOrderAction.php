<?php

namespace Modules\Pos\Application\Actions\QrOrder;

use Modules\Pos\Application\DTO\QrOrderData;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Domain\Entities\OrderQueue;

class SubmitOrderAction
{
    public function __construct(
        private TableRepositoryInterface $tableRepository,
    ) {}

    /**
     * Submit a QR order for a table.
     * Finds or creates an active session and adds the order to the queue.
     * QR orders always use pay-later flow.
     *
     * @throws \RuntimeException If table not found.
     */
    public function execute(int $tableId, QrOrderData $data): OrderQueue
    {
        $table = $this->tableRepository->findById($tableId);

        if ($table === null) {
            throw new \RuntimeException('Meja tidak ditemukan.');
        }

        $session = $this->tableRepository->findActiveSession($tableId);

        if ($session === null) {
            $session = $this->tableRepository->createSession($tableId);
        }

        return $this->tableRepository->addToOrderQueue($session->id, $data);
    }
}
