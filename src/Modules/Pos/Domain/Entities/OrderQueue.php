<?php

namespace Modules\Pos\Domain\Entities;

use DateTimeImmutable;
use Modules\Pos\Domain\Enums\OrderStatus;

class OrderQueue
{
    public function __construct(
        public ?int $id,
        public int $tableSessionId,
        public int $outletId,
        /** @var array<array{product_id: int, variant_id: int|null, quantity: int, name: string, price: float}> */
        public array $items = [],
        public OrderStatus $status = OrderStatus::Pending,
        public ?string $customerName = null,
        public ?string $notes = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}
}
