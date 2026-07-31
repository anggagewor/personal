<?php

namespace Modules\Pos\Application\DTO;

readonly class QrOrderData
{
    public function __construct(
        /** @var array<array{productId: int, variantId: int|null, quantity: int, productName: string, variantName: string|null, unitPrice: float}> */
        public array $items,
        public ?string $notes = null,
    ) {}
}
