<?php

namespace Modules\Pos\Domain\Contracts;

use Modules\Pos\Application\DTO\QrOrderData;
use Modules\Pos\Domain\Entities\OrderQueue;
use Modules\Pos\Domain\Entities\Table;
use Modules\Pos\Domain\Entities\TableSession;

interface TableRepositoryInterface
{
    public function findByOutlet(int $outletId): array;

    public function findById(int $id): ?Table;

    public function create(int $outletId, string $name): Table;

    public function delete(int $id): void;

    public function createSession(int $tableId): TableSession;

    public function closeSession(int $sessionId): void;

    public function findActiveSession(int $tableId): ?TableSession;

    public function addToOrderQueue(int $sessionId, QrOrderData $data): OrderQueue;

    public function findPendingOrders(int $outletId): array;

    public function acceptOrder(int $orderId): OrderQueue;
}
