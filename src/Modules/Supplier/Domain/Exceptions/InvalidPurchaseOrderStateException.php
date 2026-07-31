<?php

namespace Modules\Supplier\Domain\Exceptions;

class InvalidPurchaseOrderStateException extends \DomainException
{
    public static function cannotTransition(string $poNumber, string $currentState, string $targetState): self
    {
        return new self("Purchase order '{$poNumber}' tidak dapat diubah dari status '{$currentState}' ke '{$targetState}'.");
    }

    public static function notAllowed(string $poNumber, string $operation, string $currentState): self
    {
        return new self("Operasi '{$operation}' tidak diizinkan pada purchase order '{$poNumber}' dengan status '{$currentState}'.");
    }
}
