<?php

namespace Modules\Pos\Application\DTO;

readonly class CloseShiftData
{
    public function __construct(
        public int $shiftId,
        public float $closingAmount,
        public ?string $notes = null,
    ) {}
}
