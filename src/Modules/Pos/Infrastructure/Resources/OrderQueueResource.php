<?php

namespace Modules\Pos\Infrastructure\Resources;

use Modules\Pos\Domain\Entities\OrderQueue;

class OrderQueueResource
{
    public static function toArray(OrderQueue $order): array
    {
        return [
            'id' => $order->id,
            'table_session_id' => $order->tableSessionId,
            'items' => $order->items,
            'status' => $order->status->value,
            'notes' => $order->notes,
            'created_at' => $order->createdAt?->format('Y-m-d\TH:i:s.000000\Z'),
        ];
    }

    public static function collection(array $orders): array
    {
        return array_map(fn (OrderQueue $order) => self::toArray($order), $orders);
    }
}
