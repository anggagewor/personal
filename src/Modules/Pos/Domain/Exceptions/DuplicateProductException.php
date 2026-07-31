<?php

namespace Modules\Pos\Domain\Exceptions;

class DuplicateProductException extends \DomainException
{
    public static function create(string $name): self
    {
        return new self("Produk dengan nama '{$name}' sudah ada di kategori ini.");
    }
}
