<?php

namespace Modules\Accounting\Domain\Exceptions;

class UnbalancedEntryException extends \DomainException
{
    public static function create(float $imbalance): self
    {
        return new self("Jurnal tidak seimbang. Selisih: " . number_format($imbalance, 2));
    }
}
