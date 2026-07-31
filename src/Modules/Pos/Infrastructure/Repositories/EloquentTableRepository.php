<?php

namespace Modules\Pos\Infrastructure\Repositories;

use DateTimeImmutable;
use Illuminate\Support\Str;
use Modules\Pos\Application\DTO\QrOrderData;
use Modules\Pos\Domain\Contracts\TableRepositoryInterface;
use Modules\Pos\Domain\Entities\OrderQueue;
use Modules\Pos\Domain\Entities\Table;
use Modules\Pos\Domain\Entities\TableSession;
use Modules\Pos\Domain\Enums\OrderStatus;
use Modules\Pos\Domain\Enums\TableSessionStatus;
use Modules\Pos\Infrastructure\Models\OrderQueueModel;
use Modules\Pos\Infrastructure\Models\TableModel;
use Modules\Pos\Infrastructure\Models\TableSessionModel;

class EloquentTableRepository implements TableRepositoryInterface
{
    public function findByOutlet(int $outletId): array
    {
        return TableModel::where('outlet_id', $outletId)
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->all();
    }

    public function findById(int $id): ?Table
    {
        $model = TableModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function create(int $outletId, string $name): Table
    {
        $model = TableModel::create([
            'outlet_id' => $outletId,
            'name' => $name,
            'token' => Str::random(64),
            'is_active' => true,
        ]);

        return $this->toEntity($model);
    }

    public function delete(int $id): void
    {
        TableModel::where('id', $id)->delete();
    }

    public function createSession(int $tableId): TableSession
    {
        $model = TableSessionModel::create([
            'table_id' => $tableId,
            'status' => TableSessionStatus::Active->value,
            'started_at' => now(),
        ]);

        return $this->toSessionEntity($model);
    }

    public function closeSession(int $sessionId): void
    {
        TableSessionModel::where('id', $sessionId)->update([
            'status' => TableSessionStatus::Closed->value,
            'closed_at' => now(),
        ]);
    }

    public function findActiveSession(int $tableId): ?TableSession
    {
        $model = TableSessionModel::where('table_id', $tableId)
            ->where('status', TableSessionStatus::Active->value)
            ->latest('started_at')
            ->first();

        return $model ? $this->toSessionEntity($model) : null;
    }

    public function addToOrderQueue(int $sessionId, QrOrderData $data): OrderQueue
    {
        $items = array_map(fn (array $item) => [
            'product_id' => $item['productId'],
            'variant_id' => $item['variantId'] ?? null,
            'quantity' => $item['quantity'],
            'name' => $item['productName'],
            'variant_name' => $item['variantName'] ?? null,
            'price' => $item['unitPrice'],
        ], $data->items);

        $session = TableSessionModel::with('table')->findOrFail($sessionId);

        $model = OrderQueueModel::create([
            'table_session_id' => $sessionId,
            'items' => $items,
            'status' => OrderStatus::Pending->value,
            'notes' => $data->notes,
        ]);

        return $this->toOrderQueueEntity($model, $session->table->outlet_id);
    }

    public function findPendingOrders(int $outletId): array
    {
        return OrderQueueModel::where('status', OrderStatus::Pending->value)
            ->whereHas('tableSession', function ($query) use ($outletId) {
                $query->whereHas('table', function ($q) use ($outletId) {
                    $q->where('outlet_id', $outletId);
                });
            })
            ->with('tableSession.table')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($model) => $this->toOrderQueueEntity($model, $outletId))
            ->all();
    }

    public function acceptOrder(int $orderId): OrderQueue
    {
        $model = OrderQueueModel::with('tableSession.table')->findOrFail($orderId);

        $model->update([
            'status' => OrderStatus::Accepted->value,
        ]);

        return $this->toOrderQueueEntity($model, $model->tableSession->table->outlet_id);
    }

    private function toEntity(TableModel $model): Table
    {
        return new Table(
            id: $model->id,
            outletId: $model->outlet_id,
            name: $model->name,
            token: $model->token,
            isActive: $model->is_active,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }

    private function toSessionEntity(TableSessionModel $model): TableSession
    {
        return new TableSession(
            id: $model->id,
            tableId: $model->table_id,
            status: TableSessionStatus::from($model->status),
            startedAt: $model->started_at ? new DateTimeImmutable($model->started_at->toDateTimeString()) : null,
            closedAt: $model->closed_at ? new DateTimeImmutable($model->closed_at->toDateTimeString()) : null,
        );
    }

    private function toOrderQueueEntity(OrderQueueModel $model, int $outletId): OrderQueue
    {
        return new OrderQueue(
            id: $model->id,
            tableSessionId: $model->table_session_id,
            outletId: $outletId,
            items: $model->items ?? [],
            status: OrderStatus::from($model->status),
            customerName: null,
            notes: $model->notes,
            createdAt: $model->created_at ? new DateTimeImmutable($model->created_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
