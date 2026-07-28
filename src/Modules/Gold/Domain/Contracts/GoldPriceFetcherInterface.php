<?php

namespace Modules\Gold\Domain\Contracts;

interface GoldPriceFetcherInterface
{
    /**
     * Fetch current gold price (per gram, in IDR).
     * Returns null if unable to fetch.
     */
    public function fetchCurrentPrice(): ?int;
}
