<?php

namespace Modules\Supplier\Domain\Exceptions;

class EmptyPurchaseOrderException extends \DomainException
{
    public static function create(string $poNumber): self
    {
        return new self("Purchase order '{$poNumber}' tidak dapat dikonfirmasi karena tidak memiliki item.");
    }
}
