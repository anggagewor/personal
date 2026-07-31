<?php

namespace Modules\Supplier\Domain\Exceptions;

class DuplicateSupplierException extends \DomainException
{
    public static function create(string $name): self
    {
        return new self("Supplier dengan nama '{$name}' sudah ada di outlet ini.");
    }
}
