<?php

namespace Modules\Pos\Application\Actions\QrOrder;

use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Domain\Entities\OrderQueue;

class AcceptOrderAction
{
    public function __construct(
        private TableRepositoryInterface $tableRepository,
    ) {}

    /**
     * Accept an order from the order queue.
     * Transaction creation happens separately via CreateTransactionAction.
     *
     * @throws \RuntimeException If order not found.
     */
    public function execute(int $orderId): OrderQueue
    {
        return $this->tableRepository->acceptOrder($orderId);
    }
}
