<?php

namespace Modules\Pos\Application\DTO;

readonly class RefundItemData
{
    public function __construct(
        public int $transactionItemId,
        public int $quantity,
    ) {}
}
