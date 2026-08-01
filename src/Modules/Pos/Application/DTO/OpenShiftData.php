<?php

namespace Modules\Pos\Application\DTO;

readonly class OpenShiftData
{
    public function __construct(
        public int $outletId,
        public int $userId,
        public string $cashierName,
        public float $openingAmount = 0,
    ) {}
}
